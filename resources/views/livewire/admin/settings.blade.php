<div class="adm-page">
<x-admin-page-header
  title="Cadangan Data"
  desc="Cadangkan dan pulihkan seluruh data situs."
  :link="route('home')"
/>

<div class="adm-grid">
  <div class="adm-card">
    <div class="adm-card-h">
      <h3>Cadangan &amp; sinkronisasi data</h3>
      <span class="tag mono">JSON</span>
    </div>
    <p style="margin-top:4px;font-size:13px;color:var(--mut)">
      Satu berkas berisi Informasi, Struktur, Proker, UMKM, Galeri, Pendaftaran, dan Pengajuan UMKM.
    </p>

    <div class="adm-fs">
      <div class="adm-btns">
        <button class="btn p" type="button" wire:click="export" wire:loading.attr="disabled" wire:target="export">
          <span wire:loading.remove wire:target="export">Ekspor cadangan semua JSON</span>
          <span wire:loading wire:target="export">Menyiapkan…</span>
        </button>
        <label class="btn o adm-up" style="cursor:pointer">
          Impor cadangan JSON
          <input type="file" accept=".json,application/json" hidden wire:model="backupFile">
        </label>
        <button class="btn o" type="button" wire:click="reloadData">Muat ulang data</button>
        <span class="mono adm-save">{{ $savedAt ? 'Tersimpan · '.$savedAt : '' }}</span>
      </div>
    </div>

    <div wire:loading wire:target="backupFile" style="font-size:12px;color:var(--mut);margin-top:8px">Membaca &amp; memulihkan berkas…</div>

    @error('backupFile')
      <small style="display:block;margin-top:8px;color:#B91C1C">{{ $message }}</small>
    @enderror
  </div>
</div>
</div>
