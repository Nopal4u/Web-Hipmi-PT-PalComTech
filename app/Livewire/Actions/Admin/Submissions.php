<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessPhoto;
use App\Models\BusinessSubmission;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class Submissions extends Component
{
    use WithFileUploads;

    public string $search = '';
    public string $statusFilter = 'semua';

    public ?int $reviewingId = null;
    public string $reviewDescription = '';

    /** @var array<int, string> */
    public array $reviewPhotos = [];

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $newPhotos = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAccess('submissions'), 403);
    }

    public function approve(int $id): void
    {
        $submission = BusinessSubmission::findOrFail($id);

        if ($submission->status === 'disetujui' && $submission->business_id) {
            session()->flash('status', 'Pengajuan ini sudah disetujui sebelumnya.');

            return;
        }

        $this->publishSubmission($submission);

        AuditLog::record('APPROVE', 'PENGAJUAN', 'menyetujui pengajuan usaha: '.$submission->brand);
        session()->flash('status', 'Pengajuan disetujui dan usaha sudah tampil di katalog.');
    }

    public function reject(int $id): void
    {
        $submission = BusinessSubmission::findOrFail($id);

        if ($submission->business_id) {
            Business::whereKey($submission->business_id)->delete();
        }

        $submission->update(['status' => 'ditolak', 'business_id' => null]);

        if ($this->reviewingId === $submission->id) {
            $this->cancelReview();
        }

        AuditLog::record('REJECT', 'PENGAJUAN', 'menolak pengajuan usaha: '.$submission->brand);
        session()->flash('status', 'Pengajuan ditolak.');
    }

    public function delete(int $id): void
    {
        $submission = BusinessSubmission::findOrFail($id);
        $submission->delete();

        if ($this->reviewingId === $id) {
            $this->cancelReview();
        }

        AuditLog::record('DELETE', 'PENGAJUAN', 'menghapus pengajuan usaha: '.$submission->brand);
        session()->flash('status', 'Pengajuan dihapus.');
    }

    public function review(int $id): void
    {
        $submission = BusinessSubmission::findOrFail($id);

        $this->reviewingId = $submission->id;
        $this->reviewDescription = (string) $submission->description;
        $this->reviewPhotos = array_values($submission->photos ?? []);
        $this->newPhotos = [];
        $this->resetValidation();
    }

    public function cancelReview(): void
    {
        $this->reset(['reviewingId', 'reviewDescription', 'reviewPhotos', 'newPhotos']);
        $this->resetValidation();
    }

    public function removeReviewPhoto(int $index): void
    {
        unset($this->reviewPhotos[$index]);
        $this->reviewPhotos = array_values($this->reviewPhotos);
    }

    public function removeNewPhoto(int $index): void
    {
        unset($this->newPhotos[$index]);
        $this->newPhotos = array_values($this->newPhotos);
    }

    public function saveReview(bool $publish = false): void
    {
        $this->validate([
            'reviewDescription' => ['required', 'string', 'max:400'],
            'reviewPhotos'      => ['array', 'max:5'],
            'newPhotos'         => ['array', 'max:5'],
            'newPhotos.*'       => ['image', 'max:4096'],
        ], [], [
            'reviewDescription' => 'deskripsi usaha',
            'newPhotos.*'       => 'foto pendukung',
        ]);

        $submission = BusinessSubmission::findOrFail($this->reviewingId);
        $wasApproved = $submission->status === 'disetujui';

        $photos = array_values($this->reviewPhotos);

        foreach ($this->newPhotos as $upload) {
            if (count($photos) >= 5) {
                break;
            }

            $photos[] = $upload->store('submissions', 'public');
        }

        $submission->update([
            'description' => trim($this->reviewDescription),
            'photos'      => array_slice($photos, 0, 5),
        ]);

        AuditLog::record('UPDATE', 'PENGAJUAN', 'memperbarui pengajuan usaha: '.$submission->brand);

        if ($publish || $wasApproved) {
            $this->publishSubmission($submission);

            session()->flash(
                'status',
                $wasApproved
                    ? 'Pengajuan diperbarui dan katalog UMKM ikut diperbarui.'
                    : 'Pengajuan disimpan dan dipublikasikan ke katalog.'
            );
        } else {
            session()->flash('status', 'Perubahan pengajuan disimpan.');
        }

        $this->cancelReview();
    }

    /**
     * Salin seluruh data pengajuan (termasuk foto) ke katalog UMKM publik.
     */
    protected function publishSubmission(BusinessSubmission $submission): void
    {
        $meta = Business::CATEGORIES[$submission->category] ?? null;

        $data = [
            'name'        => $submission->brand,
            'category'    => $submission->category,
            'color'       => $meta['color'] ?? '#2563EB',
            'founder'     => trim($submission->founder.' · '.$submission->study_program, ' ·'),
            'description' => $submission->description,
            'instagram'   => $submission->instagram,
            'whatsapp'    => preg_replace('/\D/', '', (string) $submission->whatsapp),
            'is_active'   => true,
        ];

        $business = $submission->business_id
            ? Business::find($submission->business_id)
            : null;

        if ($business) {
            $business->update($data);
            BusinessPhoto::where('business_id', $business->id)->delete();
        } else {
            $data['sort_order'] = (int) Business::max('sort_order') + 1;
            $business = Business::create($data);
        }

        foreach (array_values($submission->photos ?? []) as $index => $path) {
            BusinessPhoto::create([
                'business_id' => $business->id,
                'path'        => $path,
                'sort_order'  => $index + 1,
            ]);
        }

        $submission->update(['status' => 'disetujui', 'business_id' => $business->id]);
    }

    public function export()
    {
        $submissions = BusinessSubmission::latest()->get();

        return response()->streamDownload(function () use ($submissions) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'Tanggal', 'Nama brand', 'Kategori', 'Deskripsi', 'Founder', 'NIM',
                'Program studi', 'Angkatan', 'WhatsApp', 'Instagram', 'Status kurasi', 'Jumlah foto',
            ]);

            foreach ($submissions as $submission) {
                fputcsv($out, [
                    $submission->created_at?->format('Y-m-d H:i:s'),
                    $this->csvText($submission->brand),
                    $this->csvText($submission->category),
                    $this->csvText($submission->description),
                    $this->csvText($submission->founder),
                    $this->csvText($submission->nim),
                    $this->csvText($submission->study_program),
                    $this->csvText($submission->year),
                    $this->csvText($submission->whatsapp),
                    $this->csvText($submission->instagram),
                    $this->csvText($submission->statusLabel()),
                    count($submission->photos ?? []),
                ]);
            }

            fclose($out);
        }, 'pengajuan-umkm-hipmi-'.now()->format('Ymd_His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function csvText($value): string
    {
        $text = (string) ($value ?? '');

        return preg_match('/^[=+\-@\t\r]/', $text) ? "'".$text : $text;
    }

    public function render()
    {
        $submissions = BusinessSubmission::query()
            ->when($this->search !== '', fn ($query) => $query->where(function ($query) {
                $query->where('brand', 'like', '%'.$this->search.'%')
                    ->orWhere('founder', 'like', '%'.$this->search.'%');
            }))
            ->when($this->statusFilter !== 'semua', fn ($query) => $query->where('status', $this->statusFilter))
            ->latest()
            ->get();

        $reviewing = $this->reviewingId
            ? BusinessSubmission::find($this->reviewingId)
            : null;

        $newPhotoPreviews = collect($this->newPhotos)->map(function ($photo) {
            try {
                return $photo->temporaryUrl();
            } catch (\Throwable $e) {
                return null;
            }
        })->filter()->values()->all();

        return view('livewire.admin.submissions', [
            'submissions'      => $submissions,
            'statuses'         => BusinessSubmission::STATUSES,
            'reviewing'        => $reviewing,
            'newPhotoPreviews' => $newPhotoPreviews,
        ]);
    }
}
