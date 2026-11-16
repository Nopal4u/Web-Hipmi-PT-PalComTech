<div class="adm-page">
<x-admin-page-header
  title="Informasi & Oprec"
  desc="Kelola pengumuman dan informasi open recruitment."
  :link="route('information')"
/>

<x-admin-metrics />

<div class="adm-bar">
  <b>Cadangan data</b>
  <button class="btn o" type="button" wire:click="export">Ekspor informasi.json</button>
  <label class="btn o" style="cursor:pointer">
    Impor
    <input type="file" accept=".json,application/json" hidden wire:model="importFile">
  </label>
  <button class="btn o" type="button" wire:click="reloadData">Muat ulang data</button>
  <select wire:model.live="statusFilter" aria-label="Filter status">
    <option value="semua">Semua status</option>
    <option value="aktif">Aktif</option>
    <option value="tutup">Ditutup</option>
  </select>
  <span class="mono adm-save" id="ais">{{ $savedAt ? 'Tersimpan · '.$savedAt : '' }}</span>
</div>

@error('importFile')
  <div class="adm-bar" style="border-color:#FECACA;background:#FEF2F2;color:#B91C1C;font-size:13px">{{ $message }}</div>
@enderror

<div class="adm-cols">
  <div class="adm-card adm-sticky">
    <div class="adm-card-h">
      <h3>{{ $editingId ? 'Edit informasi' : 'Tambah informasi' }}</h3>
      @if ($editingId)
        <span class="tag mono">Mode edit</span>
      @endif
    </div>

    <form wire:submit="save">
      <div class="adm-fs">
        <h4>Informasi utama</h4>
        <label class="adm-lb">Judul informasi *
          <input type="text" wire:model="title" maxlength="160" placeholder="mis. Open Recruitment Batch II">
          @error('title') <small style="color:#B91C1C">{{ $message }}</small> @enderror
        </label>

        <div class="adm-row2">
          <label class="adm-lb">Kategori
            <select wire:model="category">
              @foreach ($categories as $name => $color)
                <option value="{{ $name }}">{{ $name }}</option>
              @endforeach
            </select>
          </label>
          <label class="adm-lb">Status
            <select wire:model="status">
              @foreach ($statuses as $key => $meta)
                <option value="{{ $key }}">{{ $meta['label'] }}</option>
              @endforeach
            </select>
          </label>
        </div>

        <label class="adm-lb">Batas waktu / tanggal
          <input type="text" wire:model="date_label" maxlength="80" placeholder="mis. 10 - 25 Nov 2026">
        </label>
      </div>

      <div class="adm-fs">
        <h4>Banner</h4>
        <label class="adm-lb">URL gambar
          <input type="text" inputmode="url" wire:model.live.debounce.400ms="banner" placeholder="https://…">
        </label>
        <div class="adm-btns">
          <label class="btn o adm-up" style="cursor:pointer">
            Unggah gambar
            <input type="file" accept="image/*" hidden wire:model="bannerFile">
          </label>
          <button class="xs ok" type="button" wire:click="removeBanner">Hapus banner</button>
        </div>
        <div wire:loading wire:target="bannerFile" style="font-size:12px;color:var(--mut)">Mengunggah…</div>
        @php $bannerPreview = $bannerFile ? $bannerFile->temporaryUrl() : $bannerUrl; @endphp
        @if ($bannerPreview)
          <div class="adm-prev" style="margin-top:8px;border-radius:10px;overflow:hidden;border:1px solid var(--line)">
            <img src="{{ $bannerPreview }}" alt="Pratinjau banner" style="display:block;width:100%;max-height:180px;object-fit:cover">
          </div>
        @endif
      </div>

      <div class="adm-fs">
        <h4>Isi informasi</h4>
        <label class="adm-lb">Ringkasan singkat
          <textarea wire:model="summary" rows="2" maxlength="150" placeholder="Maks. 150 karakter, tampil di kartu"></textarea>
        </label>
        <label class="adm-lb">Deskripsi lengkap / persyaratan
          <textarea wire:model="description" rows="6" maxlength="5000" placeholder="Tulis rincian, linimasa, dan persyaratan. Enter = baris baru."></textarea>
        </label>
      </div>

      <div class="adm-fs">
        <h4>Tombol aksi</h4>
        <div class="adm-row2">
          <label class="adm-lb">Label tombol
            <input type="text" wire:model="action_label" maxlength="40" placeholder="Daftar Sekarang">
          </label>
          <label class="adm-lb">Tautan tombol
            <input type="text" inputmode="url" wire:model="action_url" placeholder="Google Form, grup, atau dokumen">
          </label>
        </div>
      </div>

      <div class="adm-fs">
        <label class="adm-chk"><input type="checkbox" wire:model="is_pinned"> Jadikan Sorotan Utama di Beranda Depan (Pin to Featured)</label>
      </div>

      <div class="adm-foot" style="display:flex;gap:8px;margin-top:18px">
        <button class="btn p" type="submit" wire:loading.attr="disabled" wire:target="save">
          {{ $editingId ? 'Simpan perubahan' : 'Tambah informasi' }}
        </button>
        @if ($editingId)
          <button class="btn o" type="button" wire:click="resetForm">Batal</button>
        @endif
      </div>
    </form>
  </div>

  <div>
    <h3 class="adm-h">Daftar informasi <span class="tag mono">{{ $total }}</span></h3>
    <div class="adm-list">
      @forelse ($items as $info)
        @php $banner = \App\Support\Media::url($info->banner); @endphp
        <div class="adm-item" wire:key="info-{{ $info->id }}">
          <div class="adm-thumb" style="--c:{{ $info->categoryColor() }}">
            @if ($banner)
              <img src="{{ $banner }}" alt="" loading="lazy">
            @endif
          </div>
          <div class="adm-it">
            <b>{{ $info->title }}</b>
            <small>{{ $info->category }}{{ $info->date_label ? ' · '.$info->date_label : '' }}</small>
          </div>
          <span class="tag mono" style="--c:{{ $info->statusColor() }}">{{ $info->statusLabel() }}</span>
          @if ($info->is_pinned)
            <span class="tag mono" style="--c:#EA580C">Sorotan</span>
          @endif
          <div class="adm-btns">
            <button class="xs ok" type="button" wire:click="togglePinned({{ $info->id }})">{{ $info->is_pinned ? 'Lepas Pin' : 'Pin' }}</button>
            <button class="xs ok" type="button" wire:click="edit({{ $info->id }})">Edit</button>
            <button class="xs ok" type="button" wire:click="toggleStatus({{ $info->id }})">{{ $info->status === 'tutup' ? 'Aktifkan' : 'Tutup' }}</button>
            <a class="xs ok" href="{{ route('information') }}" target="_blank" rel="noopener">↗ Preview di Web</a>
            <button class="xs ok" type="button" wire:click="toggleHidden({{ $info->id }})">{{ $info->is_hidden ? 'Tampilkan' : 'Sembunyikan' }}</button>
            <button class="xs" type="button" wire:click="delete({{ $info->id }})"
                    wire:confirm="Hapus informasi ini?">Hapus</button>
          </div>
        </div>
      @empty
        <p class="empty">Belum ada informasi. Tambahkan lewat formulir di samping.</p>
      @endforelse
    </div>
  </div>
</div>
</div>
