@php
  $menu = [
      'home'        => 'Beranda',
      'about'       => 'Tentang',
      'divisions'   => 'Bidang',
      'team'        => 'Struktur',
      'programs'    => 'Program Kerja',
      'information' => 'Informasi',
      'gallery'     => 'Galeri',
      'businesses'  => 'Bisnis & UMKM',
      'join'        => 'Bergabung',
  ];
@endphp

<header>
  <div class="w nav">
    <a class="logo" href="{{ route('home') }}" wire:navigate>
      <img src="{{ asset('images/hipmi.webp') }}" alt="Logo HIPMI PT">HIPMI PT
    </a>

    <nav class="links" id="nv" aria-label="Navigasi utama">
      <span id="pill"></span>
      @foreach ($menu as $route => $label)
        <a href="{{ route($route) }}"
           data-k="{{ $route }}"
           class="{{ request()->routeIs($route) ? 'on' : '' }}"
           wire:navigate>{{ $label }}</a>
      @endforeach
    </nav>

    <button id="menu" aria-label="Buka menu" aria-controls="dr" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>

<div class="drawer" id="dr">
  @foreach ($menu as $route => $label)
    <a href="{{ route($route) }}" data-k="{{ $route }}" style="--i:{{ $loop->index }}" wire:navigate>{{ $label }}</a>
  @endforeach
</div>
