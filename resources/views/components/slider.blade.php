@props([
    'photos' => [],
    'ratio' => '4/3',
    'color' => '#2563EB',
    'name' => '',
    'alt' => '',
    'badge' => false,
    'dots' => false,
    'start' => 0,
    'lightboxType' => null,
    'lightboxId' => null,
    'zoomable' => false,
    'badgeLabel' => null,
])

@php
    $photos = array_values(array_filter($photos));
    $count = count($photos);
    $gradient = "linear-gradient(135deg,{$color},color-mix(in srgb,{$color} 50%,#1E40AF))";
    $initial = mb_strtoupper(mb_substr(trim($name) ?: '?', 0, 1));
@endphp

<div class="sl {{ $zoomable ? 'zm' : '' }}" style="--ar:{{ $ratio }}"
     x-data="{
        i: {{ (int) $start }},
        n: {{ max($count, 1) }},
        go(d) {
            const t = this.$refs.track, total = t.children.length;
            const cur = Math.round(t.scrollLeft / Math.max(1, t.clientWidth));
            t.scrollTo({ left: ((cur + d + total) % total) * t.clientWidth, behavior: 'smooth' });
        },
        jump(j) {
            const t = this.$refs.track;
            t.scrollTo({ left: j * t.clientWidth, behavior: 'smooth' });
        }
     }">
  <div class="sl-t" x-ref="track" tabindex="0"
       @scroll.debounce.120ms="i = Math.round($refs.track.scrollLeft / Math.max(1, $refs.track.clientWidth))">
    @if ($count)
      @foreach ($photos as $j => $src)
        <div class="sl-i"
             @if ($lightboxType) data-lightbox="{{ $lightboxType }}" data-id="{{ $lightboxId }}" data-index="{{ $j }}" @endif
             @if ($lightboxType) style="cursor:zoom-in" @endif>
          <img src="{{ $src }}"
               alt="{{ $alt ?: $name }}{{ $count > 1 ? ' – foto '.($j + 1) : '' }}"
               loading="{{ $j === (int) $start ? 'eager' : 'lazy' }}"
               decoding="async" draggable="false">
        </div>
      @endforeach
    @else
      <div class="sl-i" style="background:{{ $gradient }}">
        <b class="sl-ph">{{ $initial }}</b>
      </div>
    @endif
  </div>

  @if ($count > 1 && $badge)
    <span class="sl-b"
          x-text="{{ $badgeLabel ? "'".$badgeLabel." ' + (i+1) + ' dari ' + n" : "(i+1) + '/' + n" }}"></span>
  @endif

  @if ($count > 1)
    <button class="sl-a l" type="button" aria-label="Foto sebelumnya" @click="go(-1)">‹</button>
    <button class="sl-a r" type="button" aria-label="Foto berikutnya" @click="go(1)">›</button>
  @endif

  @if ($count > 1 && $dots)
    <div class="sl-d">
      @for ($j = 0; $j < $count; $j++)
        <i :class="{ on: i === {{ $j }} }"></i>
      @endfor
    </div>
  @endif
</div>
