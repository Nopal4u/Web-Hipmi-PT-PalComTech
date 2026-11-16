<section class="pg on" id="p-struktur">
  <div class="w">
    <x-section-heading
      tag="Struktur"
      title="Dewan Pengurus Organisasi"
      description="Himpunan Pengusaha Muda Indonesia — PT ITB PalComTech" />

    <div class="tabs tm-tabs" id="tmTabs" role="group" aria-label="Filter divisi pengurus">
      @foreach ($tabs as $tab)
        <button class="tab {{ $filter === $tab['key'] ? 'on' : '' }}"
                wire:click="$set('filter','{{ $tab['key'] }}')"
                aria-pressed="{{ $filter === $tab['key'] ? 'true' : 'false' }}">
          {{ $tab['label'] }}<small>{{ $tab['count'] }}</small>
        </button>
      @endforeach
    </div>

    <div class="tm-grid" id="tmGrid" aria-live="polite">
      @forelse ($visible as $member)
        @php
          $isDosen = $member->profile_type === 'dosen';
          $photo = \App\Support\Media::url($member->photo);
          $year = $member->shortYear();
          $badge = $member->badgeLabel();
        @endphp
        <article class="tm" style="--c:{{ $member->accent() }};--i:{{ min($loop->index, 14) }}">
          <button class="tm-ph" type="button"
                  data-lightbox="member" data-id="{{ $member->id }}"
                  aria-label="Lihat profil {{ $member->name }}">
            @if ($photo)
              <img src="{{ $photo }}" alt="Foto {{ $member->name }}" loading="lazy">
            @else
              <span class="ini" aria-hidden="true">{{ $member->initial() }}</span>
            @endif
            <span class="tm-bd">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.6 13.4l-7.2 7.2a2 2 0 01-2.8 0L3 13V3h10l7.6 7.6a2 2 0 010 2.8z"/><circle cx="7.5" cy="7.5" r="1.2" fill="currentColor"/></svg>
              <span>{{ $badge }}</span>
            </span>
          </button>
          <div class="tm-in">
            <h3>{{ $member->name }}</h3>
            <p class="tm-r">{{ $member->position }}</p>
            @if (! $isDosen && $member->expertise)
              <p class="tm-kh">{{ $member->expertise }}</p>
            @endif

            @php
              $rows = $isDosen
                  ? collect([
                      $member->faculty ? ['icon' => 'M2 9l10-5 10 5-10 5zM6 11v5c0 1.5 3 3 6 3s6-1.5 6-3v-5', 'text' => $member->faculty] : null,
                      ($member->nidn || $member->expertise) ? ['icon' => 'M8 3h9a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a3 3 0 013-3zM9 8h6M9 12h6', 'text' => collect([$member->nidn ? 'NIDN '.$member->nidn : null, $member->expertise])->filter()->join(' · ')] : null,
                  ])->filter()
                  : collect([
                      ($member->study_program || $year) ? ['icon' => 'M2 9l10-5 10 5-10 5zM6 11v5c0 1.5 3 3 6 3s6-1.5 6-3v-5', 'text' => collect([$member->study_program, $year])->filter()->join(' ')] : null,
                  ])->filter();

              $rows = $rows->push($member->business ? ['icon' => 'M3 8h18v11H3zM9 8V5h6v3M3 13h18', 'text' => 'Bisnis '.$member->business] : null)->filter();
            @endphp

            @if ($rows->isNotEmpty())
              <div class="tm-bz">
                @foreach ($rows as $row)
                  <div title="{{ strip_tags($row['text']) }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $row['icon'] }}"/></svg>
                    <span>{!! $row['text'] === 'Bisnis '.$member->business ? '<b style="font-weight:600">Bisnis</b> '.e($member->business) : e($row['text']) !!}</span>
                  </div>
                @endforeach
              </div>
            @endif

            @php
              $links = collect([
                  ($cvUrl = \App\Support\Url::external($member->cv_url)) ? ['url' => $cvUrl, 'label' => $isDosen ? 'Profil Dosen / CV' : 'Lihat CV'] : null,
                  ($portfolioUrl = \App\Support\Url::external($member->portfolio_url)) ? ['url' => $portfolioUrl, 'label' => 'Portofolio'] : null,
              ])->filter();
              $socials = collect([
                  ($linkedin = \App\Support\Url::external($member->linkedin)) ? ['url' => $linkedin, 'label' => 'LinkedIn', 'class' => 'li', 'icon' => 'M4.98 3.5a2.5 2.5 0 11-.02 5 2.5 2.5 0 01.02-5zM3 9.75h4V21H3zM9.5 9.75h3.8v1.6h.05c.55-1 1.9-2 3.9-2 4.1 0 4.75 2.7 4.75 6.2V21h-4v-4.9c0-1.2 0-2.7-1.7-2.7s-1.95 1.3-1.95 2.6V21h-4z', 'fill' => true] : null,
                  $member->instagram ? ['url' => 'https://instagram.com/'.ltrim($member->instagram, '@'), 'label' => 'Instagram', 'class' => 'ig', 'icon' => 'M3 3h18v18H3zM12 8a4 4 0 100 8 4 4 0 000-8zM17.5 6.5h.01', 'fill' => false] : null,
              ])->filter();
            @endphp

            @if ($links->isNotEmpty() || $socials->isNotEmpty())
              <div class="tm-act">
                @if ($links->isNotEmpty())
                  <div class="tm-lks">
                    @foreach ($links as $link)
                      <a class="tm-cv" href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer">{{ $link['label'] }} <span aria-hidden="true">↗</span></a>
                    @endforeach
                  </div>
                @endif
                @if ($socials->isNotEmpty())
                  <div class="tm-so">
                    @foreach ($socials as $social)
                      <a class="{{ $social['class'] }}" href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $social['label'] }} {{ $member->name }}" title="{{ $social['label'] }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" @if ($social['fill']) fill="currentColor" @else fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" @endif aria-hidden="true"><path d="{{ $social['icon'] }}"/></svg>
                      </a>
                    @endforeach
                  </div>
                @endif
              </div>
            @endif
          </div>
        </article>
      @empty
        <p class="empty">Belum ada data pengurus untuk filter ini.</p>
      @endforelse
    </div>
  </div>
</section>

<script type="application/json" data-hipmi-data="members">@json($payload)</script>
