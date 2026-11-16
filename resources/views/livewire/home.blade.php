@php
  $featured = $items->first();
  $side = $items->slice(1);
  $hmTypes = [
      'dok'  => ['label' => 'Dokumentasi Kegiatan', 'color' => '#0D9488', 'icon' => 'M3 8h4l2-3h6l2 3h4v12H3zM12 17a4 4 0 100-8 4 4 0 000 8z'],
      'info' => ['label' => 'Pengumuman Penting', 'color' => '#EA580C', 'icon' => 'M4 10v4h3l6 4V6L7 10zM17 9a4 4 0 010 6'],
  ];
@endphp

<section class="pg hero on" id="p-beranda">
  <div class="w">
    <div class="hg">
      <div>
        <span class="badge">
          <img src="{{ asset('images/hipmi.webp') }}" alt="">
          <img src="{{ asset('images/itb.webp') }}" alt="">
          Official Student Chapter — Himpunan Pengusaha Muda Indonesia ITB PalComTech
        </span>

        <h1>Mencetak Pengusaha Muda Berdaya Saing Digital dari Kampus.</h1>

        <p class="lead">HIPMI PT PalComTech adalah wadah kolaboratif mahasiswa Institut Teknologi dan Bisnis PalComTech untuk mentransformasi ide kreatif menjadi bisnis nyata yang scalable. Kami memadukan jiwa wirausaha tangguh dengan pemanfaatan teknologi modern — didukung mentoring langsung dari pengusaha senior BPC HIPMI, akses pasar, dan ekosistem kampus yang suportif.</p>
      </div>

      <div class="met">
        <div><b data-n="30" data-s="+">0</b><span>Anggota aktif &amp; founder mahasiswa</span></div>
        <div><b data-n="6">0</b><span>Divisi strategis</span></div>
        <div><b data-n="24" data-s="+">0</b><span>Program kerja &amp; workshop per tahun</span></div>
        <div><b data-n="100" data-s="%">0</b><span>Praktik bisnis nyata, bukan sekadar teori</span></div>
      </div>
    </div>

    <div class="hm" aria-labelledby="hm-jd">
      <div class="hm-h">
        <h2 id="hm-jd">Kabar &amp; Aktivitas Terbaru HIPMI PT PalComTech</h2>
        <div class="tabs" id="hm-tabs" role="group" aria-label="Filter kabar beranda">
          <button class="tab {{ $filter === 'all' ? 'on' : '' }}" wire:click="$set('filter','all')">Semua Terkini</button>
          <button class="tab {{ $filter === 'dok' ? 'on' : '' }}" wire:click="$set('filter','dok')">Dokumentasi Kegiatan</button>
          <button class="tab {{ $filter === 'info' ? 'on' : '' }}" wire:click="$set('filter','info')">Pengumuman &amp; Info</button>
        </div>
      </div>

      @if ($featured)
        @php $meta = $hmTypes[$featured['type']]; @endphp
        <div class="hm-g{{ $side->isEmpty() ? ' solo' : '' }}">
          <article class="card inf-card hm-f" style="--c:{{ $meta['color'] }}">
            <div class="inf-ban" style="--c:{{ $meta['color'] }}">
              @if ($featured['cover'])
                <img src="{{ $featured['cover'] }}" alt="" loading="lazy">
              @else
                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $meta['icon'] }}"/></svg>
              @endif
            </div>
            <div class="inf-body">
              <div class="inf-meta">
                <span class="tag mono inf-tag" style="--c:{{ $meta['color'] }}">{{ $meta['label'] }}</span>
                @if ($featured['type'] === 'info')
                  @php $status = \App\Models\Information::STATUSES[$featured['status']] ?? \App\Models\Information::STATUSES['buka']; @endphp
                  <span class="tag mono inf-tag inf-dot" style="--c:{{ $status['color'] }}">{{ $status['label'] }}</span>
                @endif
              </div>
              <h3 class="inf-title">
                <button data-lightbox="{{ $featured['type'] === 'dok' ? 'gallery' : 'info' }}"
                        data-id="{{ $featured['id'] }}" data-index="0">{{ $featured['title'] }}</button>
              </h3>
              @if ($featured['date'] || $featured['count'])
                <div class="inf-date mono">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16v14H4zM4 10h16M8 3v4M16 3v4"/></svg>
                  <span>{{ collect([$featured['date'], $featured['type'] === 'dok' ? 'Album: '.$featured['count'].' Foto' : null])->filter()->join(' · ') }}</span>
                </div>
              @endif
              <p class="inf-sum">{{ $featured['summary'] }}</p>
              <div class="inf-act">
                <button class="btn p"
                        data-lightbox="{{ $featured['type'] === 'dok' ? 'gallery' : 'info' }}"
                        data-id="{{ $featured['id'] }}" data-index="0">
                  {{ $featured['type'] === 'dok' ? 'Lihat Foto & Zoom Layar Penuh' : 'Baca Syarat & Ketentuan' }}
                </button>
              </div>
            </div>
          </article>

          @if ($side->isNotEmpty())
            <div class="hm-s">
              @foreach ($side as $item)
                @php $meta = $hmTypes[$item['type']]; @endphp
                <article class="card hm-i" style="--c:{{ $meta['color'] }}">
                  <div class="inf-ban hm-th" style="--c:{{ $meta['color'] }}">
                    @if ($item['cover'])
                      <img src="{{ $item['cover'] }}" alt="" loading="lazy">
                    @else
                      <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $meta['icon'] }}"/></svg>
                    @endif
                  </div>
                  <div class="hm-b">
                    <div class="inf-meta">
                      <span class="tag mono inf-tag" style="--c:{{ $meta['color'] }}">{{ $meta['label'] }}</span>
                    </div>
                    <h3 class="inf-title">
                      <button data-lightbox="{{ $item['type'] === 'dok' ? 'gallery' : 'info' }}"
                              data-id="{{ $item['id'] }}" data-index="0">{{ $item['title'] }}</button>
                    </h3>
                    @if ($item['date'] || $item['count'])
                      <div class="inf-date mono">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16v14H4zM4 10h16M8 3v4M16 3v4"/></svg>
                        <span>{{ collect([$item['date'], $item['type'] === 'dok' ? 'Album: '.$item['count'].' Foto' : null])->filter()->join(' · ') }}</span>
                      </div>
                    @endif
                  </div>
                </article>
              @endforeach
            </div>
          @endif
        </div>
      @else
        <p class="empty">Belum ada kabar terbaru untuk kategori ini.</p>
      @endif

      <div class="hm-ft">
        <a class="btn o" href="{{ route('gallery') }}" wire:navigate>Lihat Seluruh Galeri Kegiatan (Arsip Lengkap)</a>
        <a class="btn o" href="{{ route('information') }}" wire:navigate>Lihat Seluruh Pengumuman</a>
      </div>
    </div>

    <div class="tick">
      <div id="tk">
        @foreach ($ticker as $line)<span>{{ $line }}</span>@endforeach
        @foreach ($ticker as $line)<span>{{ $line }}</span>@endforeach
      </div>
    </div>
  </div>
</section>

<script type="application/json" data-hipmi-data="albums">@json($albumPayload)</script>

<script type="application/json" data-hipmi-data="information">@json($infoPayload)</script>
