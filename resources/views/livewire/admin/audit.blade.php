<div class="adm-page">
<x-admin-page-header
  title="Audit Activity Log"
  desc="Riwayat siapa mengubah, mengunggah, menyetujui, dan menghapus data di panel admin."
  :link="route('home')"
/>

<x-admin-metrics />

<div class="adm-bar">
  <div class="adm-search">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari deskripsi atau aktor…" aria-label="Cari log">
  </div>
  <select wire:model.live="actionFilter" aria-label="Filter aksi">
    <option value="semua">Semua aksi</option>
    @foreach ($actions as $action)
      <option value="{{ $action }}">{{ $action }}</option>
    @endforeach
  </select>
  <select wire:model.live="moduleFilter" aria-label="Filter modul">
    <option value="semua">Semua modul</option>
    @foreach ($modules as $module)
      <option value="{{ $module }}">{{ $module }}</option>
    @endforeach
  </select>
  <span class="sp" style="flex:1"></span>
  @if (auth()->user()?->isSuperAdmin())
    <button class="btn o" type="button" wire:click="export" wire:loading.attr="disabled" wire:target="export">
      <span wire:loading.remove wire:target="export">Ekspor CSV</span>
      <span wire:loading wire:target="export">Menyiapkan…</span>
    </button>
  @endif
  <span class="mono" style="font-size:12px;color:var(--mut)">{{ $logs->count() }} catatan</span>
</div>

<div class="adm-wrap">
  <table class="adm-tb">
    <thead>
      <tr>
        <th>Waktu</th>
        <th>Aktor</th>
        <th>Aksi</th>
        <th>Modul</th>
        <th>Keterangan</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($logs as $log)
        @php
          $actionColor = match ($log->action) {
              'DELETE', 'REJECT' => '#EF4444',
              'CREATE', 'APPROVE' => '#10B981',
              default => '#2563EB',
          };
        @endphp
        <tr>
          <td class="mono">{{ $log->created_at?->format('d M Y H:i') }}</td>
          <td><b>{{ $log->actor }}</b></td>
          <td><span class="adm-pill" style="--c:{{ $actionColor }}">{{ $log->action }}</span></td>
          <td class="mono">{{ $log->module }}</td>
          <td>{{ $log->description }}</td>
        </tr>
      @empty
        <tr><td colspan="5" class="empty">Belum ada aktivitas yang tercatat.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
</div>
