<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessSubmission;
use App\Models\GalleryAlbum;
use App\Models\Information;
use App\Models\Program;
use App\Models\Registration;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Overview extends Component
{
    /** Label divisi yang berwenang, dipakai dialog "Akses Ditolak". */
    public const MODULE_OWNER = [
        'businesses'    => 'Divisi Pengembangan UMKM',
        'submissions'   => 'Divisi Pengembangan UMKM',
        'gallery'       => 'Divisi Media & Kominfo',
        'programs'      => 'Divisi Media & Kominfo',
        'info'          => 'Divisi Media & Kominfo',
        'team'          => 'Super Admin',
        'registrations' => 'Super Admin',
        'settings'      => 'Super Admin',
        'accounts'      => 'Super Admin',
        'audit'         => 'Super Admin',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAccess('overview'), 403);
    }

    public function render()
    {
        $activeBusinesses = Business::where('is_active', true)->count();
        $totalBusinesses = Business::count();
        $pendingSubmissions = BusinessSubmission::where('status', 'menunggu')->count();
        $totalSubmissions = BusinessSubmission::count();
        $totalAlbums = GalleryAlbum::count();
        $runningPrograms = Program::where('status', 'berjalan')->count();
        $totalPrograms = Program::count();
        $activeInfo = Information::where('status', '!=', 'tutup')->count();
        $totalInfo = Information::count();
        $newRegistrations = Registration::where('status', 'baru')->count();
        $totalRegistrations = Registration::count();

        $modules = [
            ['route' => 'admin.businesses', 'module' => 'businesses', 'label' => 'Katalog Bisnis UMKM', 'desc' => $activeBusinesses.' aktif dari '.$totalBusinesses.' usaha', 'count' => $totalBusinesses],
            ['route' => 'admin.submissions', 'module' => 'submissions', 'label' => 'Inbox Pengajuan UMKM', 'desc' => $pendingSubmissions.' menunggu review', 'count' => $totalSubmissions],
            ['route' => 'admin.gallery', 'module' => 'gallery', 'label' => 'Galeri Kegiatan', 'desc' => $totalAlbums.' album dokumentasi', 'count' => $totalAlbums],
            ['route' => 'admin.programs', 'module' => 'programs', 'label' => 'Program Kerja', 'desc' => $runningPrograms.' sedang berjalan', 'count' => $totalPrograms],
            ['route' => 'admin.info', 'module' => 'info', 'label' => 'Informasi & Oprec', 'desc' => $activeInfo.' informasi aktif', 'count' => $totalInfo],
            ['route' => 'admin.registrations', 'module' => 'registrations', 'label' => 'Inbox Pendaftaran', 'desc' => $newRegistrations > 0 ? $newRegistrations.' pendaftar baru menunggu' : 'Tidak ada pendaftar baru', 'count' => $totalRegistrations],
            ['route' => 'admin.team', 'module' => 'team', 'label' => 'Struktur Pengurus', 'desc' => 'Susunan pengurus, CV, dan portofolio', 'count' => null],
            ['route' => 'admin.settings', 'module' => 'settings', 'label' => 'Cadangan Data & Sandi', 'desc' => 'Ekspor, impor, dan ganti kata sandi', 'count' => null],
        ];

        return view('livewire.admin.overview', compact('modules'));
    }
}
