@props(['title', 'desc' => '', 'link' => null])

<div class="adm-ph">
  <div>
    <h1>{{ $title }}</h1>
    @if ($desc !== '')
      <p>{{ $desc }}</p>
    @endif
  </div>
  <div class="adm-btns">
    {{ $slot }}
    @if ($link)
      <a class="btn o" href="{{ $link }}" target="_blank" rel="noopener">↗ Lihat tampilan di web</a>
    @endif
  </div>
</div>
