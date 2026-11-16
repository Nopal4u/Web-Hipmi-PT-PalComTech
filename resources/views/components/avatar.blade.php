@props([
    'name' => '?',
    'color' => '#2563EB',
    'size' => 48,
    'photo' => null,
])

@if ($photo)
  <img class="av ph"
       src="{{ $photo }}"
       alt="{{ $name }}"
       style="width:{{ $size }}px;height:{{ $size }}px">
@else
  <span class="av"
        style="width:{{ $size }}px;height:{{ $size }}px;font-size:{{ $size * 0.4 }}px;background:linear-gradient(135deg,{{ $color }},color-mix(in srgb,{{ $color }} 50%,#1E40AF))"
        aria-hidden="true">{{ mb_strtoupper(mb_substr(trim($name) ?: '?', 0, 1)) }}</span>
@endif
