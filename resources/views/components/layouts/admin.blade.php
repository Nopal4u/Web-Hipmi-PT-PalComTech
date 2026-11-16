@php
  $crumbs = [
      'admin.overview'      => 'Ringkasan',
      'admin.businesses'    => 'Katalog Bisnis UMKM',
      'admin.submissions'   => 'Inbox Pengajuan UMKM',
      'admin.gallery'       => 'Galeri Kegiatan',
      'admin.programs'      => 'Program Kerja',
      'admin.info'          => 'Informasi & Oprec',
      'admin.team'          => 'Struktur Pengurus',
      'admin.registrations' => 'Inbox Pendaftaran',
      'admin.settings'      => 'Cadangan Data',
      'admin.accounts'      => 'Manajemen Akun Admin',
      'admin.audit'         => 'Audit Activity Log',
      'admin.password'      => 'Ganti Kata Sandi',
  ];
  $crumb = $crumb ?? ($crumbs[request()->route()?->getName()] ?? 'Ringkasan');
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? 'Panel Admin — HIPMI PT PalComTech' }}</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="{{ asset('css/hipmi.css') }}">

  @livewireStyles
  @stack('styles')
</head>

<body class="is-admin">

  <section class="pg on authed" id="p-admin">
    <div class="adm-shell">

      <x-admin-sidebar />

      <div class="adm-ovl" onclick="document.getElementById('p-admin').classList.remove('nav-open')"></div>

      <div class="adm-main">
        <div class="adm-top">
          <button class="adm-burger" type="button" aria-label="Buka menu admin"
                  onclick="document.getElementById('p-admin').classList.add('nav-open')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
          </button>
          <div class="adm-crumb">
            <span class="dim">Dashboard</span><span class="dim">/</span>
            <b>{{ $crumb ?? 'Ringkasan' }}</b>
          </div>
          <span class="sp"></span>
          <span class="adm-livepill">Live</span>
          <a class="sbtn" href="{{ route('home') }}" target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17L17 7"/><path d="M8 7h9v9"/></svg>
            <span>Buka Web</span>
          </a>
          <div class="adm-id">
            <span class="adm-av" aria-hidden="true">{{ auth()->user()?->initial() ?? 'A' }}</span>
            <span>Selamat bertugas, <b>{{ auth()->user()->name ?? 'Admin' }}</b></span>
            <span class="rb" style="--c:{{ auth()->user()?->roleColor() ?? '#2563EB' }}">{{ auth()->user()?->roleLabel() ?? 'Admin' }}</span>
          </div>
        </div>

        <div class="adm-work">
          @if (session('status'))
            <div class="adm-bar" style="border-color:#A7F3D0;background:#ECFDF5;color:#047857;font-weight:600;font-size:13px">
              {{ session('status') }}
            </div>
          @endif

          {{ $slot }}
        </div>
      </div>
    </div>
  </section>

  <dialog id="adlg"></dialog>

  @livewireScripts
  <script src="{{ asset('js/hipmi.js') }}" defer></script>
  @stack('scripts')
</body>

</html>
