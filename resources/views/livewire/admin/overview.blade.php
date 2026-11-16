<div class="adm-page">
<x-admin-page-header
  title="Ringkasan"
  desc="Ikhtisar konten yang tampil di web HIPMI. Pilih modul untuk mulai mengelola."
  :link="route('home')"
/>

<x-admin-metrics />

<div class="adm-card">
  <div class="adm-card-h">
    <h3>Kelola modul</h3>
  </div>
  <div class="ov-list">
    @foreach ($modules as $module)
      @php
        $allowed = auth()->user()?->canAccess($module['module']);
        $owner = \App\Livewire\Admin\Overview::MODULE_OWNER[$module['module']] ?? 'Super Admin';
      @endphp

      @if ($allowed)
        <a class="ov-row" href="{{ route($module['route']) }}" wire:navigate>
          <span class="ic">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
          </span>
          <span>
            <b>{{ $module['label'] }}</b>
            <small>{{ $module['desc'] }}</small>
          </span>
          <span class="n">{{ $module['count'] ?? '' }}</span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
        </a>
      @else
        <button class="ov-row" type="button" data-admin-lock data-wew="{{ $owner }}">
          <span class="ic">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
          </span>
          <span>
            <b>{{ $module['label'] }}</b>
            <small>{{ $module['desc'] }}</small>
          </span>
          <span class="n">{{ $module['count'] ?? '' }}</span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </button>
      @endif
    @endforeach
  </div>
</div>
</div>
