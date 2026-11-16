@props([
    'tag' => null,
    'title' => null,
    'description' => null,
    'center' => false,
])

<div class="sh" @if ($center) style="max-width:720px;margin-inline:auto;text-align:center" @endif>
  @if ($tag)
    <span class="tag mono">{{ $tag }}</span>
  @endif
  @if ($title)
    <h2 style="margin-top:12px">{{ $title }}</h2>
  @endif
  @if ($description)
    <p>{{ $description }}</p>
  @endif
</div>
