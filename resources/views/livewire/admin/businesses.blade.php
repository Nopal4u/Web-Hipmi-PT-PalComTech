<div class="adm-page">
<x-admin-page-header
  title="Katalog Bisnis UMKM"
  desc="Kelola brand usaha mahasiswa yang tampil di katalog publik."
  :link="route('businesses')"
/>

<x-admin-metrics />

<div class="adm-tools">
  <div class="adm-search">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari usaha mahasiswa…" aria-label="Cari usaha">
  </div>
  <select wire:model.live="categoryFilter" aria-label="Filter kategori">
    <option value="semua">Semua kategori</option>
    @foreach ($categories as $key => $meta)
      <option value="{{ $key }}">{{ $meta['label'] }}</option>
    @endforeach
  </select>
  <button class="btn p" type="button" wire:click="openPanel">+ Tambah Data Baru</button>
</div>

<div class="adm-tcard">
  <div class="adm-wrap">
    <table class="adm-tb adm-dt">
      <thead>
        <tr>
          <th>Foto</th>
          <th>Nama brand</th>
          <th>Founder</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($businesses as $business)
          @php
            $logo = \App\Support\Media::url($business->logo);
            $cover = $business->photos->first();
            $thumb = $logo ?: ($cover ? \App\Support\Media::url($cover->path) : '');
          @endphp
          <tr>
            <td class="c-th">
              <div class="adm-thumb" style="--c:{{ $business->color }}{{ $thumb ? ';background-image:url(\''.$thumb.'\');background-size:cover;background-position:center' : '' }}"></div>
            </td>
            <td class="c-nm">
              <b>{{ $business->name }}</b>
              <span class="adm-kat">{{ $business->category }}</span>
            </td>
            <td class="c-ds">{{ $business->founder }}@if ($business->photos->count()) · {{ $business->photos->count() }} foto @endif</td>
            <td class="c-st">
              <span class="tag mono" style="--c:{{ $business->is_active ? '#10B981' : '#F59E0B' }}">
                {{ $business->is_active ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
            <td class="c-ac">
              <div class="adm-btns">
                <button class="xs ok" type="button" wire:click="toggleActive({{ $business->id }})">{{ $business->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                <button class="adm-ic" type="button" title="Edit" aria-label="Edit {{ $business->name }}" wire:click="edit({{ $business->id }})">
                  <x-icon-edit />
                </button>
                <button class="adm-ic del" type="button" title="Hapus" aria-label="Hapus {{ $business->name }}" wire:click="delete({{ $business->id }})"
                        wire:confirm="Hapus usaha &quot;{{ $business->name }}&quot;?">
                  <x-icon-trash />
                </button>
              </div>
            </td>
          </tr>
        @empty
          <tr class="er">
            <td colspan="5" class="empty">Belum ada usaha terdaftar. Klik “+ Tambah Data Baru” untuk mulai.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="adm-tf">
    <span>Total usaha mahasiswa</span>
    <span class="tag mono">{{ $total }}</span>
  </div>
</div>

<div class="mf {{ $panelOpen ? 'open' : '' }}" role="dialog" aria-modal="true" aria-label="Formulir usaha mahasiswa">
  <div class="adm-card adm-sticky">
    <div class="adm-card-h">
      <h3>{{ $editingId ? 'Edit usaha' : 'Tambah usaha' }}</h3>
      @if ($editingId)
        <span class="tag mono">Mode edit</span>
      @endif
      <button type="button" class="adm-x" aria-label="Tutup formulir" wire:click="closePanel">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
      </button>
    </div>

    <form wire:submit="save">
      <div class="adm-fs">
        <label class="adm-lb">Nama usaha / brand *
          <input type="text" wire:model="name" maxlength="100" placeholder="mis. Kopi Kampus">
          @error('name') <small style="color:#B91C1C">{{ $message }}</small> @enderror
        </label>

        <div class="adm-row2">
          <label class="adm-lb">Kategori produk
            <select wire:model="category">
              @foreach ($categories as $key => $meta)
                <option value="{{ $key }}">{{ $meta['label'] }}</option>
              @endforeach
            </select>
          </label>
          <label class="adm-lb">Warna aksen
            <input type="color" wire:model="color" style="height:42px;padding:4px">
          </label>
        </div>

        <label class="adm-lb">Founder &amp; program studi *
          <input type="text" wire:model="founder" maxlength="120" placeholder="Nama · Program studi">
          @error('founder') <small style="color:#B91C1C">{{ $message }}</small> @enderror
        </label>

        <label class="adm-lb">Deskripsi produk &amp; profil usaha *
          <textarea wire:model="description" rows="4" maxlength="400"></textarea>
          @error('description') <small style="color:#B91C1C">{{ $message }}</small> @enderror
        </label>

        <div class="adm-row2">
          <label class="adm-lb">Nomor WhatsApp pemesanan
            <input type="tel" wire:model="whatsapp" maxlength="20" placeholder="0812xxxxxxx">
            @error('whatsapp') <small style="color:#B91C1C">{{ $message }}</small> @enderror
          </label>
          <label class="adm-lb">Akun Instagram (tanpa @)
            <input type="text" wire:model="instagram" maxlength="40" placeholder="kopikampus">
          </label>
        </div>

        <label class="adm-chk"><input type="checkbox" wire:model="is_active"> Tampilkan di katalog publik</label>
      </div>

      <div class="adm-fs">
        <h4>Logo &amp; foto produk</h4>
        <div class="adm-row2">
          <label class="btn o" style="cursor:pointer;justify-content:center">
            Unggah logo
            <input type="file" accept="image/*" hidden wire:model="logo">
          </label>
          <label class="btn o" style="cursor:pointer;justify-content:center">
            Unggah foto produk (maks. 5)
            <input type="file" accept="image/*" multiple hidden wire:model="photos">
          </label>
        </div>
        <div wire:loading wire:target="logo,photos" style="font-size:12px;color:var(--mut)">Mengunggah…</div>
        @error('photos.*') <small style="color:#B91C1C">{{ $message }}</small> @enderror

        @if ($logoUrl)
          <div style="margin-top:8px">
            <small style="color:var(--mut);font-size:12px">Logo saat ini</small>
            <div class="wz-th has" style="width:80px;height:80px;border-radius:50%">
              <img src="{{ $logoUrl }}" alt="Logo usaha">
            </div>
          </div>
        @endif

        @if (! empty($previews) || ($editing && $editing->photos->isNotEmpty()))
          <div class="wz-ths" style="grid-template-columns:repeat(5,1fr);margin-top:8px">
            @foreach ($previews as $preview)
              <div class="wz-th has"><img src="{{ $preview }}" alt="Pratinjau foto"></div>
            @endforeach
            @if ($editing)
              @foreach ($editing->photos as $photo)
                <div class="wz-th has">
                  <img src="{{ \App\Support\Media::url($photo->path) }}" alt="Foto usaha">
                  <button type="button" wire:click="removePhoto({{ $photo->id }})" aria-label="Hapus foto">×</button>
                </div>
              @endforeach
            @endif
          </div>
        @endif
      </div>

      <div class="adm-foot" style="display:flex;gap:8px;margin-top:18px">
        <button class="btn p" type="submit">{{ $editingId ? 'Simpan perubahan' : 'Tambah' }}</button>
        @if ($editingId)
          <button class="btn o" type="button" wire:click="closePanel">Batal</button>
        @endif
      </div>
    </form>
  </div>
</div>
<div class="adm-dov" wire:click="closePanel"></div>
</div>
