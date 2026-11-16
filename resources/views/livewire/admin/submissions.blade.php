<div class="adm-page">
<x-admin-page-header
  title="Inbox Pengajuan UMKM"
  desc="Kurasi usaha yang diajukan mahasiswa: setujui, edit, atau tolak sebelum tampil di katalog."
  :link="route('businesses')"
/>

<x-admin-metrics />

@if ($reviewing)
  <div class="adm-card" style="margin-bottom:18px">
    <div class="adm-card-h">
      <div>
        <h3>Review pengajuan</h3>
        <small style="color:var(--mut);font-size:12px">
          {{ $reviewing->brand }} · {{ $reviewing->founder }} ({{ $reviewing->nim }})
        </small>
      </div>
      <button class="adm-x" type="button" wire:click="cancelReview" aria-label="Tutup formulir review">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
      </button>
    </div>

    <div class="adm-fs">
      <label class="adm-lb">Deskripsi usaha &amp; produk unggulan *
        <textarea wire:model="reviewDescription" rows="5" maxlength="400"></textarea>
        @error('reviewDescription') <small style="color:#B91C1C">{{ $message }}</small> @enderror
      </label>

      <div>
        <div class="adm-card-h" style="border:0;padding:0;margin-bottom:8px">
          <strong style="font-size:13px">Foto pendukung</strong>
          <span class="mono" style="font-size:12px;color:var(--mut)">{{ count($reviewPhotos) + count($newPhotos) }} / 5</span>
        </div>

        <div class="wz-ths" style="grid-template-columns:repeat(5,1fr)">
          @foreach ($reviewPhotos as $index => $path)
            <div class="wz-th has">
              <img src="{{ \App\Support\Media::url($path) }}" alt="Foto pengajuan">
              <button type="button" wire:click="removeReviewPhoto({{ $index }})" aria-label="Hapus foto">×</button>
            </div>
          @endforeach

          @foreach ($newPhotoPreviews as $index => $preview)
            <div class="wz-th has">
              <img src="{{ $preview }}" alt="Pratinjau foto baru">
              <button type="button" wire:click="removeNewPhoto({{ $index }})" aria-label="Hapus foto">×</button>
            </div>
          @endforeach
        </div>

        <div style="margin-top:10px">
          <label class="btn o" style="cursor:pointer">
            + Tambah foto
            <input type="file" accept="image/*" multiple hidden wire:model="newPhotos">
          </label>
          <div wire:loading wire:target="newPhotos" style="display:inline-block;margin-left:8px;font-size:12px;color:var(--mut)">Mengunggah…</div>
        </div>

        @error('newPhotos.*') <small style="display:block;margin-top:6px;color:#B91C1C">{{ $message }}</small> @enderror
      </div>
    </div>

    <div class="adm-foot" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:18px">
      <button class="btn o" type="button" wire:click="saveReview(false)">Simpan</button>
      @if ($reviewing->status === 'disetujui')
        <button class="btn p" type="button" wire:click="saveReview(false)">Simpan &amp; perbarui katalog</button>
      @else
        <button class="btn p" type="button" wire:click="saveReview(true)">✓ Simpan &amp; Publikasikan</button>
      @endif
    </div>
  </div>
@endif

<div class="adm-bar">
  <div class="adm-search">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari brand atau founder…" aria-label="Cari pengajuan">
  </div>
  <select wire:model.live="statusFilter" aria-label="Filter status">
    <option value="semua">Semua status</option>
    @foreach ($statuses as $key => $meta)
      <option value="{{ $key }}">{{ $meta['label'] }}</option>
    @endforeach
  </select>
  <span class="sp" style="flex:1"></span>
  <button class="btn o" type="button" wire:click="export" wire:loading.attr="disabled" wire:target="export">
    <span wire:loading.remove wire:target="export">Ekspor CSV / Excel</span>
    <span wire:loading wire:target="export">Menyiapkan…</span>
  </button>
  <span class="mono" style="font-size:12px;color:var(--mut)">{{ $submissions->count() }} pengajuan</span>
</div>

<div class="adm-wrap">
  <table class="adm-tb">
    <thead>
      <tr>
        <th>Tanggal</th>
        <th>Brand &amp; deskripsi</th>
        <th>Kategori</th>
        <th>Founder · NIM · Prodi</th>
        <th>Kontak</th>
        <th>Status kurasi</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($submissions as $submission)
        @php
          $photos = collect($submission->photos ?? [])->map(fn ($path) => \App\Support\Media::url($path))->filter();
          $digits = preg_replace('/\D/', '', (string) $submission->whatsapp);
          $wa = str_starts_with($digits, '0')
              ? '62'.substr($digits, 1)
              : (str_starts_with($digits, '8') ? '62'.$digits : $digits);
          $description = (string) $submission->description;
        @endphp
        <tr>
          <td class="mono">{{ $submission->created_at?->format('d M Y H:i') }}</td>
          <td style="max-width:280px">
            <b>{{ $submission->brand }}</b>
            <br>
            <small style="color:var(--mut)">
              {{ \Illuminate\Support\Str::limit($description, 90) }}
            </small>
            @if ($photos->isNotEmpty())
              <br>
              <small class="mono" style="color:var(--mut)">{{ $photos->count() }} foto</small>
            @endif
          </td>
          <td><span class="tag mono">{{ $submission->category }}</span></td>
          <td>
            {{ $submission->founder }}
            <br>
            <small class="mono" style="color:var(--mut)">{{ $submission->nim }} · {{ $submission->study_program }}</small>
          </td>
          <td class="mono">
            @if ($wa)
              <a target="_blank" rel="noopener noreferrer"
                 href="https://wa.me/{{ $wa }}?text={{ urlencode('Halo '.$submission->founder.', kami dari Divisi UMKM HIPMI terkait pengajuan usaha '.$submission->brand.'.') }}">{{ $submission->whatsapp }}</a>
            @endif
            @if ($submission->instagram)
              <br>
              <a target="_blank" rel="noopener noreferrer"
                 href="https://instagram.com/{{ ltrim($submission->instagram, '@') }}">{{ '@'.ltrim($submission->instagram, '@') }}</a>
            @endif
          </td>
          <td>
            <span class="tag mono" style="--c:{{ $submission->statusColor() }}">{{ $submission->statusLabel() }}</span>
          </td>
          <td>
            <div class="adm-btns">
              @if ($submission->status !== 'disetujui')
                <button class="xs ok" type="button" wire:click="approve({{ $submission->id }})">
                  ✓ Setujui &amp; Publikasikan
                </button>
              @endif
              <button class="xs" type="button" wire:click="review({{ $submission->id }})">
                ↻ Review / Edit
              </button>
              @if ($submission->status === 'ditolak')
                <button class="xs" type="button" wire:click="delete({{ $submission->id }})"
                        wire:confirm="Hapus pengajuan &quot;{{ $submission->brand }}&quot; secara permanen?">Hapus</button>
              @else
                <button class="xs" type="button" wire:click="reject({{ $submission->id }})"
                        wire:confirm="Tolak pengajuan &quot;{{ $submission->brand }}&quot;?{{ $submission->status === 'disetujui' ? ' Usaha ini juga akan ditarik dari Katalog UMKM publik.' : '' }}">Tolak / Hapus</button>
              @endif
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="empty">Belum ada pengajuan yang cocok.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
</div>
