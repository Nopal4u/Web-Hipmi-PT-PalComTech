@php
  use App\Models\Business;
  use App\Support\Media;

  $prodi = ['Bisnis Digital', 'Sistem Informasi', 'Informatika', 'Manajemen', 'Akuntansi', 'DKV'];
@endphp

<section class="pg on" id="p-umkm" x-data="{ step: 1 }">
  <div class="w">
    <x-section-heading
      tag="Bisnis & UMKM"
      title="Usaha yang dirintis anggota"
      description="Dukung usaha mahasiswa: pesan langsung lewat WhatsApp atau kunjungi Instagram mereka." />

    <div class="grid" id="ul">
      @forelse ($businesses as $business)
        @php
          $color = $business->color ?: (Business::CATEGORIES[$business->category]['color'] ?? '#2563EB');
          $logo = Media::url($business->logo);
        @endphp
        <article class="card" style="--c:{{ $color }}">
          <x-slider
            :photos="$business->photos->map(fn ($photo) => Media::url($photo->path))->all()"
            ratio="1/1"
            dots
            :color="$color"
            :name="$business->name"
            :alt="$business->name"
            :badge="true"
            lightbox-type="business"
            :lightbox-id="$business->id" />

          <div class="uh" style="margin-top:14px">
            <x-avatar :name="$business->name" :color="$color" :size="52" :photo="$logo" />
            <div>
              <h3>{{ $business->name }}</h3>
              <small>{{ $business->founder }}</small>
            </div>
          </div>

          <span class="tag mono">{{ $business->category }}</span>
          <p style="margin-top:10px;font-size:14px;color:#475569">{{ $business->description }}</p>

          <div class="ua">
            <a class="btn wa" target="_blank" rel="noopener noreferrer" href="{{ $business->waLink() }}">WhatsApp</a>
            @if ($business->instagram)
              <a class="btn ig" target="_blank" rel="noopener noreferrer"
                 href="https://instagram.com/{{ ltrim($business->instagram, '@') }}">Instagram</a>
            @endif
          </div>
        </article>
      @empty
        <p class="empty">Belum ada usaha yang ditampilkan.</p>
      @endforelse
    </div>

    <div class="ub">
      <div>
        <b>Punya usaha dan ingin tampil di sini?</b><br>
        <span style="color:var(--mut);font-size:14px">Untuk mahasiswa aktif. Setiap pengajuan dikurasi Divisi UMKM HIPMI sebelum tampil.</span>
      </div>
      <button class="btn p" type="button" @click="$refs.wz.showModal(); step = 1">+ Daftarkan Usahamu</button>
    </div>
  </div>

  {{-- Wizard pengajuan usaha: 3 langkah + panel sukses (PRD UMKM §3) --}}
  <dialog x-ref="wz" class="wz" @close="step = 1">
    @if ($submitted)
      @php $cat = Business::CATEGORIES[$category] ?? ['label' => $category, 'icon' => '•']; @endphp
      <div class="wz-ok">
        <svg viewBox="0 0 72 72" aria-hidden="true">
          <circle cx="36" cy="36" r="24" />
          <path d="M26 37l8 8 14-16" />
        </svg>
        <h3 style="margin-top:10px;padding:0">Pengajuan terkirim!</h3>
        <p class="wz-sub">Terima kasih, {{ $founder }}. Tim kami akan meninjau usahamu.</p>
        <dl>
          <dt>NAMA BRAND USAHA</dt>
          <dd>{{ $brand }}</dd>
          <dt>KATEGORI USAHA</dt>
          <dd>{{ $cat['icon'] }} {{ $cat['label'] }}</dd>
          <dt>FOUNDER &amp; JURUSAN</dt>
          <dd>{{ $founder }} · {{ $study_program }}</dd>
          <dt>STATUS</dt>
          <dd>⏳ Menunggu Verifikasi Divisi UMKM HIPMI</dd>
        </dl>
        <div class="wz-nav">
          <button class="btn o" type="button" @click="$wire.resetSubmission(); step = 1">↺ Buat Pengajuan Lain</button>
          <button class="btn p" type="button" @click="$refs.wz.close()">Selesai</button>
        </div>
      </div>
    @else
      <h3>Pendaftaran usaha mahasiswa</h3>
      <p class="wz-sub">Usahamu dikurasi Divisi UMKM HIPMI sebelum tampil di katalog.</p>

      <div class="wz-st">
        <div class="wz-ln"><i :style="'--p:' + ((step - 1) * 50) + '%'"></i></div>
        <div class="wz-n" :class="step > 1 ? 'ok' : (step === 1 ? 'on' : '')">
          <b><span x-show="step > 1">✓</span><span x-show="step <= 1">1</span></b>
          <span>Data Mahasiswa</span>
        </div>
        <div class="wz-n" :class="step > 2 ? 'ok' : (step === 2 ? 'on' : '')">
          <b><span x-show="step > 2">✓</span><span x-show="step <= 2">2</span></b>
          <span>Profil Usaha</span>
        </div>
        <div class="wz-n" :class="step === 3 ? 'on' : ''">
          <b>3</b>
          <span>Foto &amp; Kontak</span>
        </div>
      </div>

      {{-- Langkah 1: Data mahasiswa --}}
      <div class="wz-f" x-show="step === 1" x-cloak>
        <label>Nama lengkap founder *
          <input wire:model.live="founder" autocomplete="name" maxlength="120" placeholder="Sesuai KTM">
          @error('founder') <small style="color:#B91C1C">{{ $message }}</small> @enderror
        </label>

        <label>Program studi *
          <select wire:model.live="study_program">
            <option value="">Pilih program studi</option>
            @foreach ($prodi as $p)
              <option value="{{ $p }}">{{ $p }}</option>
            @endforeach
          </select>
          @error('study_program') <small style="color:#B91C1C">{{ $message }}</small> @enderror
        </label>

        <label>Angkatan masuk *
          <input wire:model.live="year" inputmode="numeric" maxlength="4" placeholder="2024">
          @error('year') <small style="color:#B91C1C">{{ $message }}</small> @enderror
        </label>

        <label>Nomor Induk Mahasiswa (NIM) *
          <small>Hanya digunakan untuk verifikasi mahasiswa aktif dan tidak dipublikasikan ke umum.</small>
          <input wire:model.live="nim" inputmode="numeric" maxlength="30">
          @error('nim') <small style="color:#B91C1C">{{ $message }}</small> @enderror
        </label>

        <div class="wz-nav">
          <button class="btn p" type="button"
                  @click="if (await $wire.validateStep(1)) step = 2">Lanjut: Profil Usaha →</button>
        </div>
      </div>

      {{-- Langkah 2: Profil usaha --}}
      <div class="wz-f" x-show="step === 2" x-cloak>
        <label>Nama brand / usaha *
          <input wire:model.live="brand" maxlength="100" placeholder="mis. Kopi Kampus">
          @error('brand') <small style="color:#B91C1C">{{ $message }}</small> @enderror
        </label>

        <div class="wz-lb">
          <span>Kategori bidang usaha *</span>
          <div class="wz-cats">
            @foreach ($categories as $key => $meta)
              <button type="button" class="wz-cat" style="--c:{{ $meta['color'] }}"
                      :class="{ on: $wire.category === '{{ $key }}' }"
                      :aria-pressed="$wire.category === '{{ $key }}'"
                      @click="$wire.set('category', '{{ $key }}')">
                <span>{{ $meta['icon'] }}</span>
                <b>{{ $meta['label'] }}</b>
                <small>{{ $meta['desc'] }}</small>
              </button>
            @endforeach
          </div>
          @error('category') <small style="color:#B91C1C">{{ $message }}</small> @enderror
        </div>

        <label>Deskripsi usaha &amp; produk unggulan *
          <textarea wire:model.live="description" rows="4" maxlength="400" placeholder="Ceritakan produk yang kamu jual dan keunikannya"></textarea>
          @error('description') <small style="color:#B91C1C">{{ $message }}</small> @enderror
        </label>

        <div class="wz-nav">
          <button class="btn o" type="button" @click="step = 1">← Kembali</button>
          <button class="btn p" type="button"
                  @click="if (await $wire.validateStep(2)) step = 3">Lanjut: Foto &amp; Kontak →</button>
        </div>
      </div>

      {{-- Langkah 3: Foto & kontak --}}
      <div class="wz-f" x-show="step === 3" x-cloak>
        <div class="wz-lb">
          <span>Foto produk / usaha <span class="mono" style="font-weight:400;color:var(--mut)">{{ count($photos) }}/5 foto</span></span>
          <label class="wz-dz">
            <b>Tarik foto ke sini atau klik untuk memilih</b>
            <small style="font-weight:400;color:var(--mut)">Hingga 5 foto, JPG/PNG/WebP maksimal 4 MB per foto.</small>
            <input type="file" accept="image/*" multiple hidden wire:model="photos">
          </label>
          <div class="wz-ths">
            @for ($i = 0; $i < 5; $i++)
              @if (isset($photos[$i]))
                <div class="wz-th has">
                  <img src="{{ $photos[$i]->temporaryUrl() }}" alt="Foto {{ $i + 1 }}">
                  <button type="button" wire:click="removePhoto({{ $i }})" aria-label="Hapus foto {{ $i + 1 }}">×</button>
                </div>
              @else
                <div class="wz-th">{{ $i + 1 }}</div>
              @endif
            @endfor
          </div>
          <div wire:loading wire:target="photos" style="font-size:12px;color:var(--mut)">Mengunggah foto…</div>
          @error('photos.*') <small style="color:#B91C1C">{{ $message }}</small> @enderror
        </div>

        <label>Nomor WhatsApp bisnis *
          <small>Kontak pemesanan produk.</small>
          <input wire:model.live="whatsapp" type="tel" inputmode="tel" maxlength="20" placeholder="0812xxxxxxx">
          @error('whatsapp') <small style="color:#B91C1C">{{ $message }}</small> @enderror
        </label>

        <label>Akun Instagram usaha (opsional)
          <input wire:model.live="instagram" maxlength="40" placeholder="kopikampus (tanpa @)">
          @error('instagram') <small style="color:#B91C1C">{{ $message }}</small> @enderror
        </label>

        <div class="wz-nav">
          <button class="btn o" type="button" @click="step = 2">← Kembali</button>
          <button class="btn p" type="button" wire:loading.attr="disabled" wire:target="submitBusiness"
                  @click="$wire.submitBusiness()">🚀 Kirim Pengajuan ke Admin HIPMI</button>
        </div>
      </div>
    @endif
  </dialog>
</section>

<script type="application/json" data-hipmi-data="businesses">@json($payload)</script>
