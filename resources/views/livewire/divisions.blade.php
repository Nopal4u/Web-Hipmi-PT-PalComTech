<section class="pg on" id="p-bidang">
  <div class="w">
    <x-section-heading
      tag="6 Divisi"
      title="Pilih bidang yang paling kamu minati"
      description="Klik kartu untuk melihat fokus, skill yang kamu dapat, dan program unggulannya." />

    <div class="grid" id="bd" style="align-items:start">
      @foreach ($divisions as $division)
        @php
          $leader = $division->members->firstWhere('position', 'Ketua Divisi') ?? $division->members->first();
          $featuredPrograms = $division->programs->take(2);
        @endphp
        <article class="card" style="--c:{{ $division->color }}" x-data="{ open: false }" :class="{ open }">
          <button class="bh" type="button" :aria-expanded="open" @click="open = !open">
            <span class="rn mono">{{ $division->numeral }}</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="{{ $division->color }}" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $division->icon }}"/></svg>
            <h3>{{ $division->name }}</h3>
            <span class="pl">+</span>
          </button>
          <p class="fo">{{ $division->focus }}</p>
          <div class="more" x-show="open" x-cloak>
            <div>
              <h4>APA YANG KAMU PELAJARI?</h4>
              <p>{{ $division->learning }}</p>
              <h4>PROGRAM UNGGULAN</h4>
              <ul>
                @forelse ($featuredPrograms as $program)
                  <li>{{ $program->title }}</li>
                @empty
                  <li>Program divisi akan diumumkan melalui halaman Informasi.</li>
                @endforelse
              </ul>
              <div class="kd">
                <x-avatar :name="$leader?->name ?? $division->leader_name ?? '?'" :color="$division->color" :size="34" />
                <span>Ketua Divisi<b>{{ $leader?->name ?? $division->leader_name ?? '—' }}</b></span>
              </div>
            </div>
          </div>
        </article>
      @endforeach
    </div>
  </div>
</section>
