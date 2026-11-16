<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessPhoto;
use App\Models\BusinessSubmission;
use App\Models\Division;
use App\Models\GalleryAlbum;
use App\Models\GalleryPhoto;
use App\Models\Information;
use App\Models\Member;
use App\Models\Program;
use App\Models\Registration;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class Settings extends Component
{
    use WithFileUploads;

    public $backupFile = null;

    public string $savedAt = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAccess('settings'), 403);
    }

    public function updated(string $name): void
    {
        if ($name === 'backupFile') {
            $this->import();
        }
    }

    public function export()
    {
        $payload = [
            'versi'         => 1,
            'dibuat'        => now()->toIso8601String(),
            'settings'      => Setting::allKeyed(),
            'divisions'     => Division::ordered()->get()->toArray(),
            'members'       => Member::ordered()->get()->toArray(),
            'programs'      => Program::with('division')->get()->toArray(),
            'information'   => Information::latest()->get()->toArray(),
            'albums'        => GalleryAlbum::with('photos')->ordered()->get()->toArray(),
            'businesses'    => Business::with('photos')->ordered()->get()->toArray(),
            'submissions'   => BusinessSubmission::latest()->get()->toArray(),
            'registrations' => Registration::latest()->get()->toArray(),
            'users'         => User::query()->get(['id', 'name', 'username', 'email', 'division', 'role', 'is_active'])->toArray(),
        ];

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        AuditLog::record('EXPORT', 'PENGATURAN', 'mengunduh cadangan data situs');

        return response()->streamDownload(
            function () use ($json) {
                echo $json;
            },
            'hipmi_data_backup.json',
            ['Content-Type' => 'application/json']
        );
    }

    public function import(): void
    {
        if (! $this->backupFile) {
            return;
        }

        $this->validate([
            'backupFile' => ['required', 'file', 'max:10240'],
        ], [], [
            'backupFile' => 'berkas cadangan JSON',
        ]);

        try {
            $payload = json_decode(
                (string) file_get_contents($this->backupFile->getRealPath()),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\Throwable $e) {
            $this->addError('backupFile', 'Berkas bukan JSON yang valid.');
            $this->reset('backupFile');

            return;
        }

        if (! is_array($payload) || ! isset($payload['versi'], $payload['settings'])) {
            $this->addError('backupFile', 'Struktur cadangan tidak dikenali.');
            $this->reset('backupFile');

            return;
        }

        DB::transaction(function () use ($payload) {
            foreach (($payload['settings'] ?? []) as $key => $value) {
                Setting::put((string) $key, is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE));
            }

            $divisionMap = [];

            foreach (($payload['divisions'] ?? []) as $row) {
                $division = Division::updateOrCreate(
                    ['id' => $row['id']],
                    $this->pickFields($row, ['numeral', 'name', 'short_name', 'color', 'icon', 'focus', 'learning', 'leader_name', 'sort_order'])
                );

                $divisionMap[$row['id']] = $division->id;
            }

            foreach (($payload['members'] ?? []) as $row) {
                $attrs = $this->pickFields($row, [
                    'group', 'name', 'position', 'profile_type', 'photo', 'expertise',
                    'study_program', 'year', 'faculty', 'nidn', 'business', 'cv_url',
                    'portfolio_url', 'linkedin', 'instagram', 'mods', 'sort_order',
                ]);
                $attrs['division_id'] = ! empty($row['division_id'])
                    ? ($divisionMap[$row['division_id']] ?? null)
                    : null;

                Member::updateOrCreate(['id' => $row['id']], $attrs);
            }

            foreach (($payload['programs'] ?? []) as $row) {
                $divisionId = $divisionMap[$row['division_id']] ?? null;

                if (! $divisionId) {
                    continue;
                }

                $attrs = $this->pickFields($row, ['title', 'description', 'schedule', 'audience', 'status', 'sort_order']);
                $attrs['division_id'] = $divisionId;

                Program::updateOrCreate(['id' => $row['id']], $attrs);
            }

            foreach (($payload['information'] ?? []) as $row) {
                Information::updateOrCreate(['id' => $row['id']], $this->pickFields($row, [
                    'title', 'category', 'date_label', 'status', 'summary', 'description',
                    'action_label', 'action_url', 'banner', 'is_pinned', 'is_hidden',
                ]));
            }

            foreach (($payload['albums'] ?? []) as $row) {
                $album = GalleryAlbum::updateOrCreate(['id' => $row['id']], $this->pickFields($row, [
                    'title', 'date_label', 'category', 'color', 'description', 'is_pinned', 'sort_order',
                ]));

                $album->photos()->delete();

                foreach (($row['photos'] ?? []) as $index => $photo) {
                    GalleryPhoto::create([
                        'gallery_album_id' => $album->id,
                        'path'             => $photo['path'],
                        'sort_order'       => $photo['sort_order'] ?? $index,
                    ]);
                }
            }

            foreach (($payload['businesses'] ?? []) as $row) {
                $business = Business::updateOrCreate(['id' => $row['id']], $this->pickFields($row, [
                    'name', 'category', 'color', 'founder', 'description',
                    'instagram', 'whatsapp', 'logo', 'is_active', 'sort_order',
                ]));

                $business->photos()->delete();

                foreach (($row['photos'] ?? []) as $index => $photo) {
                    BusinessPhoto::create([
                        'business_id' => $business->id,
                        'path'        => $photo['path'],
                        'sort_order'  => $photo['sort_order'] ?? $index,
                    ]);
                }
            }

            foreach (($payload['submissions'] ?? []) as $row) {
                BusinessSubmission::updateOrCreate(['id' => $row['id']], $this->pickFields($row, [
                    'brand', 'category', 'description', 'founder', 'study_program',
                    'year', 'nim', 'whatsapp', 'instagram', 'status', 'business_id', 'photos',
                ]));
            }

            foreach (($payload['registrations'] ?? []) as $row) {
                Registration::updateOrCreate(['id' => $row['id']], $this->pickFields($row, [
                    'name', 'study_program', 'whatsapp', 'motivation', 'status',
                ]));
            }
        });

        $this->reset('backupFile');
        $this->touch();

        AuditLog::record('IMPORT', 'PENGATURAN', 'memulihkan data dari cadangan JSON');
        session()->flash('status', 'Cadangan data berhasil diimpor.');
    }

    public function reloadData(): void
    {
        $this->reset('backupFile');
        $this->touch();
        session()->flash('status', 'Data dimuat ulang dari server.');
    }

    /** Ambil hanya kunci yang boleh diisi dari satu baris cadangan. */
    protected function pickFields(array $row, array $keys): array
    {
        return array_intersect_key($row, array_flip($keys));
    }

    protected function touch(): void
    {
        $this->savedAt = now()->format('H:i:s');
    }

    public function render()
    {
        return view('livewire.admin.settings');
    }
}
