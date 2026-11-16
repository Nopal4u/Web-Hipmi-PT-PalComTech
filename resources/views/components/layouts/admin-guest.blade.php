<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? 'Masuk Panel Admin — HIPMI PT PalComTech' }}</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="{{ asset('css/hipmi.css') }}">

  @livewireStyles
  @stack('styles')
</head>

<body class="is-admin">

  <section class="pg on" id="p-admin">
    <div class="adm-shell">
      <div class="adm-main">
        <div class="adm-work">
          {{ $slot }}
        </div>
      </div>
    </div>
  </section>

  @livewireScripts
  <script src="{{ asset('js/hipmi.js') }}" defer></script>
  @stack('scripts')
</body>

</html>
