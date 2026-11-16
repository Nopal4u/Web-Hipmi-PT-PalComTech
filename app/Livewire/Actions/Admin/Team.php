<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Division;
use App\Models\Member;
use App\Support\Media;
use App\Support\Url;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class Team extends Component
{
    use WithFileUploads;

    /**
     * Judul, tombol, dan placeholder untuk tiap opsi tambahan (modul) pada form profil.
     */
    public const MODULE_META = [
        'bisnis' => [
            'button' => '+ Unit Bisnis',
            'title'  => 'Unit bisnis mahasiswa / praktisi',
            'title_dosen' => 'Unit bisnis praktisi',
        ],
        'cv' => [
            'button' => '+ Link CV',
            'title'  => 'Berkas CV / profil akademik (Google Drive / PDF)',
        ],
        'porto' => [
            'button' => '+ Portofolio',
            'title'  => 'Link portofolio (website / Behance / GitHub)',
        ],
        'sosmed' => [
            'button' => '+ Jejaring Medsos',
            'title'  => 'Jejaring medsos (LinkedIn & Instagram)',
        ],
    ];

    /** @var array<int, array<string, mixed>> State form tiap pengurus, dikunci id. */
    public array $people = [];

    /** @var array<int, mixed> Berkas foto yang sedang diunggah per pengurus. */
    public array $photoUploads = [];

    public string $savedAt = '';

    public $importFile = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAccess('team'), 403);

        $this->loadPeople();
    }

    /** Muat seluruh pengurus dari basis data ke state form. */
    protected function loadPeople(): void
    {
        $this->people = Member::ordered()->get()
            ->mapWithKeys(fn (Member $member) => [$member->id => [
                'id'            => $member->id,
                'group'         => $member->group,
                'division_id'   => $member->division_id,
                'name'          => (string) $member->name,
                'position'      => (string) $member->position,
                'profile_type'  => $member->profile_type === 'dosen' ? 'dosen' : 'mahasiswa',
                'expertise'     => (string) $member->expertise,
                'study_program' => (string) $member->study_program,
                'year'          => (string) $member->year,
                'faculty'       => (string) $member->faculty,
                'nidn'          => (string) $member->nidn,
                'business'      => (string) $member->business,
                'cv_url'        => (string) $member->cv_url,
                'portfolio_url' => (string) $member->portfolio_url,
                'linkedin'      => (string) $member->linkedin,
                'instagram'     => (string) $member->instagram,
                'mods'          => array_values((array) $member->mods),
                'photo'         => (string) $member->photo,
            ]])
            ->all();
    }

    /** Opsi tambahan aktif untuk satu state profil: ditambah admin atau sudah terisi. */
    public static function activeModules(array $person): array
    {
        $active = collect((array) ($person['mods'] ?? []));

        foreach (Member::MODULES as $module => $fields) {
            foreach ($fields as $field) {
                if (filled($person[$field] ?? null)) {
                    $active->push($module);

                    break;
                }
            }
        }

        return $active->unique()
            ->filter(fn ($module) => isset(Member::MODULES[$module]))
            ->values()
            ->all();
    }

    public function updated(string $name, $value): void
    {
        if ($name === 'importFile') {
            $this->import();

            return;
        }

        if (str_starts_with($name, 'photoUploads.')) {
            $this->storePhoto((int) substr($name, strlen('photoUploads.')));

            return;
        }

        if (! str_starts_with($name, 'people.')) {
            return;
        }

        $parts = explode('.', $name);

        if (count($parts) < 3) {
            return;
        }

        $id = (int) $parts[1];
        $field = $parts[2];

        if ($field === 'profile_type') {
            $this->clearProfileFields($id);
        }

        $this->persist($id);
    }

    /** Simpan satu baris pengurus dari state ke basis data. */
    protected function persist(int $id): void
    {
        $member = Member::find($id);
        $row = $this->people[$id] ?? null;

        if (! $member || ! $row) {
            return;
        }

        $member->fill([
            'name'          => trim((string) ($row['name'] ?? '')),
            'position'      => trim((string) ($row['position'] ?? '')),
            'profile_type'  => ($row['profile_type'] ?? '') === 'dosen' ? 'dosen' : 'mahasiswa',
            'expertise'     => trim((string) ($row['expertise'] ?? '')),
            'study_program' => trim((string) ($row['study_program'] ?? '')),
            'year'          => trim((string) ($row['year'] ?? '')),
            'faculty'       => trim((string) ($row['faculty'] ?? '')),
            'nidn'          => trim((string) ($row['nidn'] ?? '')),
            'business'      => trim((string) ($row['business'] ?? '')),
            'cv_url'        => Url::external($row['cv_url'] ?? null) ?? '',
            'portfolio_url' => Url::external($row['portfolio_url'] ?? null) ?? '',
            'linkedin'      => Url::external($row['linkedin'] ?? null) ?? '',
            'instagram'     => trim((string) ($row['instagram'] ?? '')),
            'mods'          => array_values((array) ($row['mods'] ?? [])),
        ]);

        if (! $member->isDirty()) {
            return;
        }

        $member->save();
        $this->touch();
        AuditLog::record('UPDATE', 'STRUKTUR', 'memperbarui pengurus: '.($member->name ?: 'tanpa nama'));
    }

    /** Kosongkan kolom akademik yang tidak berlaku saat kategori profil diganti. */
    protected function clearProfileFields(int $id): void
    {
        if (! isset($this->people[$id])) {
            return;
        }

        if (($this->people[$id]['profile_type'] ?? '') === 'dosen') {
            $this->people[$id]['study_program'] = '';
            $this->people[$id]['year'] = '';
        } else {
            $this->people[$id]['faculty'] = '';
            $this->people[$id]['nidn'] = '';
        }
    }

    protected function storePhoto(int $id): void
    {
        $file = $this->photoUploads[$id] ?? null;
        $member = Member::find($id);

        if (! $file || ! $member) {
            unset($this->photoUploads[$id]);

            return;
        }

        $path = $file->store('members', 'public');

        $member->update(['photo' => $path]);
        $this->people[$id]['photo'] = $path;
        unset($this->photoUploads[$id]);

        $this->touch();
        AuditLog::record('UPDATE', 'STRUKTUR', 'memperbarui foto pengurus: '.($member->name ?: 'tanpa nama'));
    }

    public function removePhoto(int $id): void
    {
        $member = Member::find($id);

        if (! $member) {
            return;
        }

        $member->update(['photo' => null]);
        $this->people[$id]['photo'] = '';

        $this->touch();
        AuditLog::record('UPDATE', 'STRUKTUR', 'menghapus foto pengurus: '.($member->name ?: 'tanpa nama'));
    }

    /**
     * Tambahkan opsi tambahan. Renderless: UI diperbarui optimistis oleh Alpine,
     * sehingga tidak perlu mengirim ulang seluruh komponen (36 pengurus).
     */
    #[Renderless]
    public function addModule(int $id, string $module): void
    {
        if (! isset(Member::MODULES[$module]) || ! isset($this->people[$id])) {
            return;
        }

        $mods = array_values((array) ($this->people[$id]['mods'] ?? []));

        if (! in_array($module, $mods, true)) {
            $mods[] = $module;
        }

        $this->people[$id]['mods'] = $mods;

        $member = Member::find($id);

        if ($member) {
            $member->update(['mods' => $mods]);
        }

        $this->touch();
    }

    public function removeModule(int $id, string $module): void
    {
        if (! isset(Member::MODULES[$module]) || ! isset($this->people[$id])) {
            return;
        }

        $fields = Member::MODULES[$module];

        foreach ($fields as $field) {
            $this->people[$id][$field] = '';
        }

        $this->people[$id]['mods'] = array_values(array_filter(
            (array) ($this->people[$id]['mods'] ?? []),
            fn ($m) => $m !== $module
        ));

        $member = Member::find($id);

        if ($member) {
            $attrs = ['mods' => $this->people[$id]['mods']];

            foreach ($fields as $field) {
                $attrs[$field] = null;
            }

            $member->update($attrs);
            $this->touch();
            AuditLog::record('UPDATE', 'STRUKTUR', 'menghapus opsi tambahan pengurus: '.($member->name ?: 'tanpa nama'));
        }
    }

    public function addMember(int $divisionId): void
    {
        $division = Division::findOrFail($divisionId);

        $sort = (int) Member::where('division_id', $divisionId)->max('sort_order');

        Member::create([
            'division_id'  => $divisionId,
            'group'        => 'division',
            'name'         => '',
            'position'     => 'Anggota',
            'profile_type' => 'mahasiswa',
            'sort_order'   => $sort + 1,
        ]);

        $this->loadPeople();
        $this->touch();
        AuditLog::record('CREATE', 'STRUKTUR', 'menambah anggota divisi: '.$division->name);
    }

    public function addDosen(): void
    {
        if (Member::where('group', 'dosen')->exists()) {
            return;
        }

        Member::create([
            'group'        => 'dosen',
            'name'         => 'Dosen Pembimbing',
            'position'     => 'Dosen Pembimbing',
            'profile_type' => 'dosen',
            'sort_order'   => 0,
        ]);

        $this->loadPeople();
        $this->touch();
        AuditLog::record('CREATE', 'STRUKTUR', 'menambah dosen pembimbing');
    }

    public function removeMember(int $id): void
    {
        $member = Member::find($id);

        if (! $member || $member->group !== 'division') {
            return;
        }

        $name = $member->name;
        $member->delete();
        unset($this->people[$id]);

        $this->touch();
        AuditLog::record('DELETE', 'STRUKTUR', 'menghapus pengurus: '.($name ?: 'tanpa nama'));
    }

    public function export()
    {
        $payload = [
            'versi'   => 1,
            'dibuat'  => now()->toIso8601String(),
            'members' => Member::with('division')->ordered()->get()->map(fn (Member $member) => [
                'group'         => $member->group,
                'division'      => $member->division?->numeral,
                'name'          => $member->name,
                'position'      => $member->position,
                'profile_type'  => $member->profile_type,
                'photo'         => $member->photo,
                'expertise'     => $member->expertise,
                'study_program' => $member->study_program,
                'year'          => $member->year,
                'faculty'       => $member->faculty,
                'nidn'          => $member->nidn,
                'business'      => $member->business,
                'cv_url'        => $member->cv_url,
                'portfolio_url' => $member->portfolio_url,
                'linkedin'      => $member->linkedin,
                'instagram'     => $member->instagram,
                'mods'          => array_values((array) $member->mods),
                'sort_order'    => $member->sort_order,
            ])->values()->all(),
        ];

        AuditLog::record('EXPORT', 'STRUKTUR', 'mengunduh cadangan struktur pengurus');

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return response()->streamDownload(
            fn () => print ($json),
            'struktur.json',
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
        ], [], ['importFile' => 'berkas struktur.json']);

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

        $rows = (is_array($payload) && array_is_list($payload))
            ? $payload
            : ($payload['members'] ?? null);

        if (! is_array($rows)) {
            $this->addError('importFile', 'Struktur cadangan tidak dikenali.');
            $this->reset('importFile');

            return;
        }

        $divisionByNumeral = Division::all()->keyBy('numeral');

        DB::transaction(function () use ($rows, $divisionByNumeral) {
            Member::query()->delete();

            foreach ($rows as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $group = in_array($row['group'] ?? '', ['dosen', 'bph', 'division'], true)
                    ? $row['group']
                    : 'division';

                $divisionId = null;

                if ($group === 'division' && ! empty($row['division'])) {
                    $divisionId = optional($divisionByNumeral->get($row['division']))->id;
                }

                Member::create([
                    'group'         => $group,
                    'division_id'   => $divisionId,
                    'name'          => (string) ($row['name'] ?? ''),
                    'position'      => (string) ($row['position'] ?? ''),
                    'profile_type'  => ($row['profile_type'] ?? '') === 'dosen' ? 'dosen' : 'mahasiswa',
                    'photo'         => $row['photo'] ?? null,
                    'expertise'     => $row['expertise'] ?? null,
                    'study_program' => $row['study_program'] ?? null,
                    'year'          => $row['year'] ?? null,
                    'faculty'       => $row['faculty'] ?? null,
                    'nidn'          => $row['nidn'] ?? null,
                    'business'      => $row['business'] ?? null,
                    'cv_url'        => Url::external($row['cv_url'] ?? null),
                    'portfolio_url' => Url::external($row['portfolio_url'] ?? null),
                    'linkedin'      => Url::external($row['linkedin'] ?? null),
                    'instagram'     => $row['instagram'] ?? null,
                    'mods'          => array_values(array_filter(
                        (array) ($row['mods'] ?? []),
                        fn ($module) => isset(Member::MODULES[$module])
                    )),
                    'sort_order'    => (int) ($row['sort_order'] ?? 0),
                ]);
            }
        });

        $this->reset('importFile');
        $this->loadPeople();
        $this->touch();

        AuditLog::record('IMPORT', 'STRUKTUR', 'memulihkan struktur pengurus dari cadangan JSON');
        session()->flash('status', 'Struktur pengurus berhasil diimpor.');
    }

    public function reloadData(): void
    {
        $this->reset('importFile');
        $this->loadPeople();
        session()->flash('status', 'Data pengurus dimuat ulang dari server.');
    }

    protected function touch(): void
    {
        $this->savedAt = now()->format('H:i:s');
    }

    public function render()
    {
        $divisions = Division::ordered()->get();
        $members = Member::with('division')->ordered()->get();

        $dosenMembers = $members->where('group', 'dosen')->values();
        $bphMembers = $members->where('group', 'bph')->values();

        // Bangun satu baris pengurus lengkap dengan status opsi tambahannya.
        $row = function (Member $member, string $label, string $groupName, bool $removable) {
            $person = $this->people[$member->id] ?? [];
            $active = self::activeModules($person);

            return [
                'id'             => $member->id,
                'label'          => $label,
                'group_name'     => $groupName,
                'removable'      => $removable,
                'active_mods'    => $active,
                'available_mods' => array_values(array_diff(array_keys(Member::MODULES), $active)),
            ];
        };

        $groups = [];

        $groups[] = [
            'key'          => 'dosen',
            'title'        => 'Dosen Pembimbing',
            'color'        => '#F59E0B',
            'count'        => $dosenMembers->count(),
            'rows'         => $dosenMembers->map(fn (Member $member) => $row($member, 'Dosen Pembimbing', 'Pembina organisasi', false))->all(),
            'add_dosen'    => $dosenMembers->isEmpty(),
            'add_division' => null,
        ];

        $groups[] = [
            'key'          => 'bph',
            'title'        => 'Pengurus Inti (BPH)',
            'color'        => '#2563EB',
            'count'        => $bphMembers->count(),
            'rows'         => $bphMembers->map(fn (Member $member) => $row($member, (string) $member->position ?: 'Pengurus Inti', 'Pengurus Inti (BPH)', false))->all(),
            'add_dosen'    => false,
            'add_division' => null,
        ];

        foreach ($divisions as $division) {
            $divisionMembers = $members->where('group', 'division')
                ->where('division_id', $division->id)
                ->values();

            $leader = $divisionMembers->first(fn (Member $member) => $member->position === 'Ketua Divisi')
                ?? $divisionMembers->first();

            $others = $leader
                ? $divisionMembers->reject(fn (Member $member) => $member->id === $leader->id)->values()
                : $divisionMembers;

            $rows = [];

            if ($leader) {
                $rows[] = $row($leader, 'Ketua Divisi', $division->name, false);
            }

            foreach ($others as $member) {
                $rows[] = $row($member, 'Anggota', $division->name, true);
            }

            $groups[] = [
                'key'          => 'div-'.$division->id,
                'title'        => 'Divisi '.$division->numeral.' · '.$division->short_name,
                'color'        => $division->color ?: '#2563EB',
                'count'        => $divisionMembers->count(),
                'rows'         => $rows,
                'add_dosen'    => false,
                'add_division' => $division->id,
            ];
        }

        return view('livewire.admin.team', [
            'groups'     => $groups,
            'moduleMeta' => self::MODULE_META,
        ]);
    }
}
