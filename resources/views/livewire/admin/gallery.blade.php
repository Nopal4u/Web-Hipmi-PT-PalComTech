<div class="adm-page">
<x-admin-page-header
  title="Galeri Kegiatan"
  desc="Kelola album dokumentasi kegiatan dan foto di dalamnya."
  :link="route('gallery')"
/>

<x-admin-metrics />

<div class="adm-tools">
  <div class="adm-search">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari dokumentasi…" aria-label="Cari album">
  </div>
  <button class="btn p" type="button" wire:click="openPanel">+ Tambah Data Baru</button>
</div>

<div class="adm-tcard">
  <div class="adm-wrap">
    <table class="adm-tb adm-dt">
      <thead>
        <tr>
          <th>Foto</th>
          <th>Judul album</th>
          <th>Waktu &amp; foto</th>
          <th>Kategori</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($albums as $album)
          @php $cover = \App\Support\Media::url($album->cover()); @endphp
          <tr>
            <td class="c-th">
              <div class="adm-thumb" style="--c:{{ $album->color }}{{ $cover ? ';background-image:url(\''.$cover.'\');background-size:cover;background-position:center' : '' }}"></div>
            </td>
            <td class="c-nm">
              <b>{{ $album->title }}</b>
              @if ($album->description)
                <span class="adm-kat">{{ \Illuminate\Support\Str::limit($album->description, 60) }}</span>
              @endif
            </td>
            <td class="c-ds">{{ $album->date_label ?: '—' }} · {{ $album->photos->count() }} foto</td>
            <td class="c-st">
              <span class="tag mono">{{ $album->category }}</span>
              @if ($album->is_pinned)
                <span class="tag mono" style="--c:#EA580C">Sorotan</span>
              @endif
            </td>
            <td class="c-ac">
              <div class="adm-btns">
                <button class="adm-ic" type="button" title="Edit" aria-label="Edit {{ $album->title }}" wire:click="edit({{ $album->id }})">
                  <x-icon-edit />
                </button>
                <button class="adm-ic del" type="button" title="Hapus" aria-label="Hapus {{ $album->title }}" wire:click="delete({{ $album->id }})"
                        wire:confirm="Hapus album &quot;{{ $album->title }}&quot; beserta fotonya?">
                  <x-icon-trash />
                </button>
              </div>
            </td>
          </tr>
        @empty
          <tr class="er">
            <td colspan="5" class="empty">Belum ada album galeri. Klik “+ Tambah Data Baru” untuk mulai.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="adm-tf">
    <span>Total dokumentasi</span>
    <span class="tag mono">{{ $total }}</span>
  </div>
</div>

<div class="mf {{ $panelOpen ? 'open' : '' }}" role="dialog" aria-modal="true" aria-label="Formulir dokumentasi">
  <div class="adm-card adm-sticky">
    <div class="adm-card-h">
      <h3>{{ $editingId ? 'Edit dokumentasi' : 'Tambah dokumentasi' }}</h3>
      @if ($editingId)
        <span class="tag mono">Mode edit</span>
      @endif
      <button type="button" class="adm-x" aria-label="Tutup formulir" wire:click="closePanel">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
      </button>
    </div>

    <form wire:submit="save">
      <div class="adm-fs">
        <label class="adm-lb">Judul agenda / kegiatan *
          <input type="text" wire:model="title" maxlength="120" placeholder="mis. Business Bootcamp 2026">
          @error('title') <small style="color:#B91C1C">{{ $message }}</small> @enderror
        </label>

        <div class="adm-row2">
          <label class="adm-lb">Kategori kegiatan
            <select wire:model="category">
              @foreach ($categories as $name)
                <option value="{{ $name }}">{{ $name }}</option>
              @endforeach
            </select>
          </label>
          <label class="adm-lb">Waktu pelaksanaan
            <input type="text" wire:model="date_label" maxlength="40" placeholder="mis. Okt 2026">
          </label>
        </div>

        <label class="adm-lb">Warna latar jika tanpa foto
          <input type="color" wire:model="color" style="height:42px;padding:4px">
        </label>

        <label class="adm-lb">Deskripsi kegiatan (tampil di lightbox)
          <textarea wire:model="description" rows="3" maxlength="400"></textarea>
        </label>

        <label class="adm-chk"><input type="checkbox" wire:model="is_pinned"> Jadikan Sorotan Utama di Beranda Depan</label>
      </div>

      <div class="adm-fs">
        <h4>Foto kegiatan (1–10, foto pertama = cover)</h4>
        <label class="btn o" style="cursor:pointer;justify-content:center">
          Unggah foto (maks. 10)
          <input type="file" accept="image/*" multiple hidden wire:model="photos">
        </label>
        <div wire:loading wire:target="photos" style="font-size:12px;color:var(--mut)">Mengunggah foto…</div>
        @error('photos.*') <small style="color:#B91C1C">{{ $message }}</small> @enderror

        @if (! empty($previews))
          <div class="wz-ths" style="grid-template-columns:repeat(5,1fr)">
            @foreach ($previews as $preview)
              <div class="wz-th has"><img src="{{ $preview }}" alt="Pratinjau foto"></div>
            @endforeach
          </div>
        @endif

        @if ($editing && $editing->photos->isNotEmpty())
          <div class="mgrid">
            @foreach ($editing->photos as $photo)
              <div class="mpt">
                <img src="{{ \App\Support\Media::url($photo->path) }}" alt="Foto album">
                @if ($loop->first)
                  <em>Cover</em>
                @endif
                <button type="button" class="rm" wire:click="removePhoto({{ $photo->id }})" aria-label="Hapus foto">×</button>
                <span class="mv">
                  @if (! $loop->first)
                    <button type="button" wire:click="movePhoto({{ $photo->id }}, 'up')" aria-label="Geser ke kiri">‹</button>
                  @else
                    <i></i>
                  @endif
                  @if (! $loop->last)
                    <button type="button" wire:click="movePhoto({{ $photo->id }}, 'down')" aria-label="Geser ke kanan">›</button>
                  @endif
                </span>
              </div>
            @endforeach
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
