<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? 'HIPMI PT PalComTech — Hub Wirausaha Muda ITB PalComTech' }}</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="{{ asset('css/hipmi.css') }}">

  @livewireStyles
  @stack('styles')
</head>

<body>

  <x-site-header />

  <main>
    {{ $slot }}
  </main>

  <x-site-footer />

  {{-- Dialog global: dipakai lightbox galeri, detail informasi, dan profil --}}
  <dialog id="dlg"></dialog>
  <dialog id="adlg"></dialog>

  @livewireScripts
  <script src="{{ asset('js/hipmi.js') }}" defer></script>
  @stack('scripts')
</body>

</html>
