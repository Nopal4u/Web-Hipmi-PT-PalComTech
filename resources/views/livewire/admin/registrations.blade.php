<div class="adm-page">
<x-admin-page-header
  title="Inbox Pendaftaran"
  desc="Pantau pendaftar baru dan perbarui status seleksi mereka."
  :link="route('join')"
/>

<x-admin-metrics />

<div class="adm-bar">
  <div class="adm-search">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama…" aria-label="Cari pendaftar">
  </div>
  <select wire:model.live="statusFilter" aria-label="Filter status">
    <option value="semua">Semua status</option>
    @foreach ($statuses as $key => $meta)
      <option value="{{ $key }}">{{ $meta['label'] }}</option>
    @endforeach
  </select>
  <span class="sp" style="flex:1"></span>
  <button class="btn o" type="button" wire:click="export" wire:loading.attr="disabled" wire:target="export">
    <span wire:loading.remove wire:target="export">Ekspor CSV (Excel)</span>
    <span wire:loading wire:target="export">Menyiapkan…</span>
  </button>
  <span class="mono" style="font-size:12px;color:var(--mut)">{{ $registrations->count() }} pendaftar</span>
</div>

<div class="adm-wrap">
  <table class="adm-tb">
    <thead>
      <tr>
        <th>Waktu daftar</th>
        <th>Nama</th>
        <th>Prodi / angkatan</th>
        <th>Kontak WA</th>
        <th>Motivasi</th>
        <th>Status seleksi</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($registrations as $registration)
        @php $wa = $registration->waNumber(); @endphp
        <tr>
          <td class="mono">{{ $registration->created_at?->format('d M Y H:i') }}</td>
          <td><b>{{ $registration->name }}</b></td>
          <td>{{ $registration->study_program ?: '—' }}</td>
          <td class="mono">
            @if ($wa)
              <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener noreferrer">{{ $registration->whatsapp }}</a>
            @else
              {{ $registration->whatsapp ?: '—' }}
            @endif
          </td>
          <td style="max-width:260px">{{ $registration->motivation ?: '—' }}</td>
          <td>
            <select style="--c:{{ $registration->statusColor() }}" aria-label="Status {{ $registration->name }}"
                    wire:change="updateStatus({{ $registration->id }}, $event.target.value)">
              @foreach ($statuses as $key => $meta)
                <option value="{{ $key }}" @selected($registration->status === $key)>{{ $meta['label'] }}</option>
              @endforeach
            </select>
          </td>
          <td>
            <div class="adm-btns">
              @if ($wa)
                <a class="xs ok" target="_blank" rel="noopener noreferrer"
                   wire:click="markContacted({{ $registration->id }})"
                   href="https://wa.me/{{ $wa }}?text={{ urlencode('Halo '.$registration->name.', kami dari tim rekrutmen HIPMI PT PalComTech.') }}">Chat WhatsApp</a>
              @endif
              <button class="xs" type="button" wire:click="delete({{ $registration->id }})"
                      wire:confirm="Hapus data pendaftar &quot;{{ $registration->name }}&quot;?">Hapus</button>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="empty">Belum ada pendaftar yang cocok.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
</div>
