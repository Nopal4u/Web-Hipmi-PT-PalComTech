@php
  $categoryIcons = [
      'Open Recruitment' => 'M12 5a3 3 0 100 6 3 3 0 000-6zM5 21c0-4 3-6.5 7-6.5S19 17 19 21',
      'Agenda & Event'   => 'M4 6h16v14H4zM4 10h16M8 3v4M16 3v4',
      'Pengumuman'       => 'M4 10v4h3l6 4V6L7 10zM17 9a4 4 0 010 6',
  ];
@endphp

<section class="pg on" id="p-informasi">
  <div class="w">
    <x-section-heading
      tag="Informasi"
      title="Kabar & Informasi"
      description="Open recruitment, agenda kegiatan, dan pengumuman terbaru seputar HIPMI PT. Klik kartu untuk membaca rinciannya." />

    <div class="tabs" id="inf-tabs" role="group" aria-label="Filter kategori informasi">
      <button class="tab {{ $filter === 'Semua' ? 'on' : '' }}" wire:click="$set('filter','Semua')">Semua</button>
      @foreach (array_keys(\App\Models\Information::CATEGORIES) as $category)
        <button class="tab {{ $filter === $category ? 'on' : '' }}" wire:click="$set('filter','{{ $category }}')">{{ $category }}</button>
      @endforeach
    </div>

    <div class="inf-grid" id="inf-list" aria-live="polite">
      @forelse ($items as $info)
        @php
          $status = \App\Models\Information::STATUSES[$info->status] ?? \App\Models\Information::STATUSES['buka'];
          $banner = \App\Support\Media::url($info->banner);
          $link = $info->action_url;
          $locked = $info->isLocked();
        @endphp
        <article class="card inf-card" style="--c:{{ $info->categoryColor() }}">
          <div class="inf-ban" style="--c:{{ $info->categoryColor() }}">
            @if ($banner)
              <img src="{{ $banner }}" alt="" loading="lazy" onerror="this.remove()">
            @else
              <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $categoryIcons[$info->category] ?? $categoryIcons['Pengumuman'] }}"/></svg>
            @endif
          </div>
          <div class="inf-body">
            <div class="inf-meta">
              <span class="tag mono inf-tag">{{ $info->category }}</span>
              <span class="tag mono inf-tag inf-dot" style="--c:{{ $status['color'] }}">{{ $status['label'] }}</span>
            </div>
            <h3 class="inf-title">
              <button data-lightbox="info" data-id="{{ $info->id }}">{{ $info->title }}</button>
            </h3>
            @if ($info->date_label)
              <div class="inf-date mono">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16v14H4zM4 10h16M8 3v4M16 3v4"/></svg>
                <span>{{ $info->date_label }}</span>
              </div>
            @endif
            <p class="inf-sum">{{ $info->summary }}</p>
            <div class="inf-act">
              @if ($link && ! $locked)
                <a class="btn p" href="{{ $link }}" target="_blank" rel="noopener noreferrer">{{ $info->action_label ?: 'Daftar Sekarang' }}</a>
              @else
                <button class="btn o" data-lightbox="info" data-id="{{ $info->id }}" aria-label="Detail: {{ $info->title }}">Detail</button>
              @endif
            </div>
          </div>
        </article>
      @empty
        <p class="empty">Belum ada informasi untuk kategori ini.</p>
      @endforelse
    </div>
  </div>
</section>

<script type="application/json" data-hipmi-data="information">@json($payload)</script>
