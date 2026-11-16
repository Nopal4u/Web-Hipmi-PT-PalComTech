@php
  use App\Models\Business;
  use App\Models\BusinessSubmission;
  use App\Models\GalleryAlbum;
  use App\Models\Information;
  use App\Models\Registration;

  $user = auth()->user();

  $pendingSubmissions = BusinessSubmission::where('status', 'menunggu')->count();
  $newRegistrations = Registration::where('status', 'baru')->count();
  $activeInfo = Information::where('status', '!=', 'tutup')->count();
  $businessCount = Business::count();
  $galleryCount = GalleryAlbum::count();

  $items = [
      [
          'route' => 'admin.overview',
          'module' => 'overview',
          'label' => 'Ringkasan',
          'wew' => 'Semua pengurus',
          'icon' => '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>',
      ],
      [
          'route' => 'admin.businesses',
          'module' => 'businesses',
          'label' => 'Katalog Bisnis UMKM',
          'wew' => 'Divisi Pengembangan UMKM',
          'icon' => '<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
          'badge' => $businessCount,
      ],
      [
          'route' => 'admin.submissions',
          'module' => 'submissions',
          'label' => 'Inbox Pengajuan UMKM',
          'wew' => 'Divisi Pengembangan UMKM',
          'icon' => '<path d="M12 3v12"/><path d="M7 10l5 5 5-5"/><path d="M5 21h14"/>',
          'badge' => $pendingSubmissions,
          'new' => $pendingSubmissions > 0,
      ],
      [
          'route' => 'admin.gallery',
          'module' => 'gallery',
          'label' => 'Galeri Kegiatan',
          'wew' => 'Divisi Media & Kominfo',
          'icon' => '<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>',
          'badge' => $galleryCount,
      ],
      [
          'route' => 'admin.programs',
          'module' => 'programs',
          'label' => 'Program Kerja',
          'wew' => 'Divisi Media & Kominfo',
          'icon' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
      ],
      [
          'route' => 'admin.info',
          'module' => 'info',
          'label' => 'Informasi & Oprec',
          'wew' => 'Divisi Media & Kominfo',
          'icon' => '<path d="M3 11v2a1 1 0 0 0 1 1h2l5 4V6L6 10H4a1 1 0 0 0-1 1z"/><path d="M15.5 8.5a5 5 0 0 1 0 7"/><path d="M18.5 5.5a9 9 0 0 1 0 13"/>',
          'badge' => $activeInfo,
      ],
      [
          'route' => 'admin.team',
          'module' => 'team',
          'label' => 'Struktur Pengurus',
          'wew' => 'Super Admin',
          'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
      ],
      [
          'route' => 'admin.registrations',
          'module' => 'registrations',
          'label' => 'Inbox Pendaftaran',
          'wew' => 'Super Admin',
          'icon' => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>',
          'badge' => $newRegistrations,
          'new' => $newRegistrations > 0,
      ],
      [
          'route' => 'admin.settings',
          'module' => 'settings',
          'label' => 'Cadangan Data',
          'wew' => 'Super Admin',
          'icon' => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>',
      ],
      [
          'route' => 'admin.accounts',
          'module' => 'accounts',
          'label' => 'Manajemen Akun Admin',
          'wew' => 'Super Admin',
          'icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/>',
      ],
      [
          'route' => 'admin.audit',
          'module' => 'audit',
          'label' => 'Audit Activity Log',
          'wew' => 'Super Admin',
          'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h8"/>',
      ],
  ];
@endphp

<aside class="adm-side" id="adm-side" aria-label="Navigasi admin">
  <div class="adm-brand"><i>H</i><span>HIPMI ADMIN</span></div>

  <nav class="adm-nav" id="anv" aria-label="Menu admin">
    @foreach ($items as $item)
      @php $locked = ! $user || ! $user->canAccess($item['module']); @endphp

      @if ($locked)
        <button type="button" class="lock" data-admin-lock data-wew="{{ $item['wew'] }}"
                title="Akses terbatas — {{ $item['wew'] }}">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
               stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $item['icon'] !!}</svg>
          <span>{{ $item['label'] }}</span>
          @if (! empty($item['badge']))
            <span class="bdg {{ ! empty($item['new']) ? 'new' : '' }}">{{ $item['badge'] }}</span>
          @endif
          <span class="lk" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                 stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </span>
        </button>
      @else
        <a href="{{ route($item['route']) }}"
           class="{{ request()->routeIs($item['route']) ? 'on' : '' }}"
           wire:navigate>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
               stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $item['icon'] !!}</svg>
          <span>{{ $item['label'] }}</span>
          @if (! empty($item['badge']))
            <span class="bdg {{ ! empty($item['new']) ? 'new' : '' }}">{{ $item['badge'] }}</span>
          @endif
        </a>
      @endif
    @endforeach
  </nav>

  <div class="adm-user">
    <span class="adm-av" aria-hidden="true">{{ $user?->initial() ?? 'A' }}</span>
    <b>{{ $user?->name ?? 'Admin' }}</b>
    <small>{{ $user?->roleLabel() ?? 'Admin' }}</small>

    <a class="adm-out" href="{{ route('admin.password') }}" wire:navigate>Ganti sandi</a>

    <form method="POST" action="{{ route('admin.logout') }}" style="grid-column:1/-1">
      @csrf
      <button class="adm-out" type="submit">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <path d="M16 17l5-5-5-5"/>
          <path d="M21 12H9"/>
        </svg>
        Keluar
      </button>
    </form>
  </div>
</aside>
