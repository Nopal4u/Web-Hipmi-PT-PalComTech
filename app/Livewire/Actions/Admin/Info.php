<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Information;
use App\Support\Media;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class Info extends Component
{
    use WithFileUploads;

    public ?int $editingId = null;

    public string $title = '';
    public string $category = 'Open Recruitment';
    public string $date_label = '';
    public string $status = 'buka';
    public string $summary = '';
    public string $description = '';
    public string $action_label = 'Daftar Sekarang';
    public string $action_url = '';
    public string $banner = '';
    public bool $is_pinned = false;

    public $bannerFile = null;
    public $importFile = null;

    public string $statusFilter = 'semua';
    public string $savedAt = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAccess('info'), 403);
    }

    protected function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'max:160'],
            'category'     => ['required', 'in:'.implode(',', array_keys(Information::CATEGORIES))],
            'date_label'   => ['nullable', 'string', 'max:80'],
            'status'       => ['required', 'in:'.implode(',', array_keys(Information::STATUSES))],
            'summary'      => ['nullable', 'string', 'max:150'],
            'description'  => ['nullable', 'string', 'max:5000'],
            'action_label' => ['nullable', 'string', 'max:40'],
            'action_url'   => ['nullable', 'string', 'max:500'],
            'banner'       => ['nullable', 'string', 'max:500'],
            'bannerFile'   => ['nullable', 'image', 'max:4096'],
            'is_pinned'    => ['boolean'],
        ];
    }

    public function updated(string $name): void
    {
        if ($name === 'importFile') {
            $this->import();
        }
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->bannerFile) {
            $data['banner'] = $this->bannerFile->store('banners', 'public');
        }

        unset($data['bannerFile']);

        if ($this->editingId) {
            $info = Information::findOrFail($this->editingId);
            $info->update($data);
            AuditLog::record('UPDATE', 'PENGUMUMAN', 'memperbarui informasi: '.$info->title);
            session()->flash('status', 'Informasi berhasil diperbarui.');
        } else {
            $info = Information::create($data);
            AuditLog::record('CREATE', 'PENGUMUMAN', 'menambah informasi: '.$info->title);
            session()->flash('status', 'Informasi berhasil ditambahkan.');
        }

        $this->touch();
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $info = Information::findOrFail($id);

        $this->editingId = $info->id;
        $this->title = $info->title;
        $this->category = $info->category;
        $this->date_label = (string) $info->date_label;
        $this->status = $info->status;
        $this->summary = (string) $info->summary;
        $this->description = (string) $info->description;
        $this->action_label = (string) $info->action_label;
        $this->action_url = (string) $info->action_url;
        $this->banner = (string) $info->banner;
        $this->is_pinned = (bool) $info->is_pinned;
        $this->bannerFile = null;
        $this->resetValidation();
    }

    public function removeBanner(): void
    {
        $this->banner = '';
        $this->bannerFile = null;
    }

    public function delete(int $id): void
    {
        $info = Information::findOrFail($id);
        $info->delete();
        AuditLog::record('DELETE', 'PENGUMUMAN', 'menghapus informasi: '.$info->title);
        session()->flash('status', 'Informasi dihapus.');

        if ($this->editingId === $id) {
            $this->resetForm();
        }
    }

    public function togglePinned(int $id): void
    {
        $info = Information::findOrFail($id);
        $info->update(['is_pinned' => ! $info->is_pinned]);

        AuditLog::record('UPDATE', 'PENGUMUMAN', ($info->is_pinned ? 'menyorot' : 'melepas sorotan').' informasi: '.$info->title);
        session()->flash('status', $info->is_pinned ? 'Informasi dijadikan sorotan utama.' : 'Sorotan informasi dilepas.');
    }

    public function toggleHidden(int $id): void
    {
        $info = Information::findOrFail($id);
        $info->update(['is_hidden' => ! $info->is_hidden]);

        AuditLog::record('UPDATE', 'PENGUMUMAN', ($info->is_hidden ? 'menyembunyikan' : 'menampilkan').' informasi: '.$info->title);
        session()->flash('status', $info->is_hidden ? 'Informasi disembunyikan dari web.' : 'Informasi kembali tampil di web.');
    }

    public function toggleStatus(int $id): void
    {
        $info = Information::findOrFail($id);
        $info->status = $info->status === 'tutup' ? 'buka' : 'tutup';
        $info->save();

        AuditLog::record('UPDATE', 'PENGUMUMAN', ($info->status === 'tutup' ? 'menutup' : 'mengaktifkan').' informasi: '.$info->title);
        session()->flash('status', $info->status === 'tutup' ? 'Informasi ditutup.' : 'Informasi diaktifkan kembali.');
    }

    public function export()
    {
        $items = Information::latest()->get()->map(fn (Information $info) => [
            'title'        => $info->title,
            'category'     => $info->category,
            'date_label'   => $info->date_label,
            'status'       => $info->status,
            'summary'      => $info->summary,
            'description'  => $info->description,
            'action_label' => $info->action_label,
            'action_url'   => $info->action_url,
            'banner'       => $info->banner,
            'is_pinned'    => (bool) $info->is_pinned,
            'is_hidden'    => (bool) $info->is_hidden,
        ])->all();

        AuditLog::record('EXPORT', 'PENGUMUMAN', 'mengunduh cadangan informasi');

        $json = json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return response()->streamDownload(
            fn () => print ($json),
            'informasi.json',
            ['Content-Type' => 'application/json']
        );
    }

    public function import(): void
    {
        if (! $this->importFile) {
            return;
        }

        $this->validate([
            'importFile' => ['required', 'file', 'max:10240'],
        ], [], ['importFile' => 'berkas informasi.json']);

        try {
            $payload = json_decode(
                (string) file_get_contents($this->importFile->getRealPath()),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\Throwable $e) {
            $this->addError('importFile', 'Berkas bukan JSON yang valid.');
            $this->reset('importFile');

            return;
        }

        if (! is_array($payload) || ! array_is_list($payload)) {
            $this->addError('importFile', 'Berkas harus berupa daftar informasi hasil ekspor.');
            $this->reset('importFile');

            return;
        }

        DB::transaction(function () use ($payload) {
            Information::query()->delete();

            foreach ($payload as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $title = trim((string) ($row['title'] ?? $row['judul'] ?? ''));

                if ($title === '') {
                    continue;
                }

                Information::create([
                    'title'        => $title,
                    'category'     => in_array($row['category'] ?? $row['kategori'] ?? '', array_keys(Information::CATEGORIES), true)
                        ? ($row['category'] ?? $row['kategori'])
                        : array_key_first(Information::CATEGORIES),
                    'date_label'   => (string) ($row['date_label'] ?? $row['tanggal'] ?? ''),
                    'status'       => in_array($row['status'] ?? '', array_keys(Information::STATUSES), true) ? $row['status'] : 'buka',
                    'summary'      => (string) ($row['summary'] ?? $row['ringkas'] ?? ''),
                    'description'  => (string) ($row['description'] ?? $row['deskripsi'] ?? ''),
                    'action_label' => (string) ($row['action_label'] ?? $row['labelAksi'] ?? 'Daftar Sekarang'),
                    'action_url'   => (string) ($row['action_url'] ?? $row['linkAksi'] ?? ''),
                    'banner'       => (string) ($row['banner'] ?? ''),
                    'is_pinned'    => (bool) ($row['is_pinned'] ?? $row['pin'] ?? false),
                    'is_hidden'    => (bool) ($row['is_hidden'] ?? $row['hide'] ?? false),
                ]);
            }
        });

        $this->reset('importFile', 'editingId');
        $this->touch();

        AuditLog::record('IMPORT', 'PENGUMUMAN', 'memulihkan informasi dari cadangan JSON');
        session()->flash('status', 'Informasi berhasil diimpor.');
    }

    public function reloadData(): void
    {
        $this->reset('importFile');
        session()->flash('status', 'Data informasi dimuat ulang dari server.');
    }

    protected function touch(): void
    {
        $this->savedAt = now()->format('H:i:s');
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId', 'title', 'date_label', 'summary', 'description',
            'action_url', 'banner', 'bannerFile', 'is_pinned',
        ]);
        $this->category = 'Open Recruitment';
        $this->status = 'buka';
        $this->action_label = 'Daftar Sekarang';
        $this->resetValidation();
    }

    public function render()
    {
        $items = Information::query()
            ->when($this->statusFilter === 'aktif', fn ($query) => $query->where('status', '!=', 'tutup'))
            ->when($this->statusFilter === 'tutup', fn ($query) => $query->where('status', 'tutup'))
            ->latest()
            ->get();

        return view('livewire.admin.info', [
            'items'      => $items,
            'total'      => Information::count(),
            'categories' => Information::CATEGORIES,
            'statuses'   => Information::STATUSES,
            'bannerUrl'  => $this->banner ? Media::url($this->banner) : null,
        ]);
    }
}
