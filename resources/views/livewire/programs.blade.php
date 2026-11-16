<section class="pg on" id="p-proker">
  <div class="w">
    <x-section-heading
      tag="Program Kerja"
      title="Agenda per bidang"
      description="Sebagian besar program terbuka untuk mahasiswa umum." />

    <div class="tabs" id="pt">
      <button class="tab {{ $filter === 'all' ? 'on' : '' }}" wire:click="$set('filter','all')">Semua</button>
      @foreach ($divisions as $division)
        <button class="tab {{ $filter === (string) $division->id ? 'on' : '' }}"
                wire:click="$set('filter','{{ $division->id }}')">{{ $division->short_name }}</button>
      @endforeach
    </div>

    <div class="grid" id="pg">
      @forelse ($programs as $program)
        @php $status = \App\Models\Program::STATUSES[$program->status] ?? \App\Models\Program::STATUSES['mendatang']; @endphp
        <div class="card pk" style="--c:{{ $program->division?->color ?? '#2563EB' }}">
          <div class="pt">
            <span class="dt mono">{{ $program->schedule }}</span>
            <span class="tag mono" style="--c:{{ $status['color'] }}">{{ $status['label'] }}</span>
          </div>
          <h3>{{ $program->title }}</h3>
          <p>{{ $program->description }}</p>
          <div class="pf">
            <span class="tag mono">{{ $program->division?->short_name ?? 'Umum' }}</span>
            <span class="mono">Target: {{ $program->audience }}</span>
          </div>
        </div>
      @empty
        <p class="empty">Belum ada program untuk bidang ini.</p>
      @endforelse
    </div>
  </div>
</section>
