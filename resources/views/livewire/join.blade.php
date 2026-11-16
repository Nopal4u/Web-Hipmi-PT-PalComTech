<section class="pg on" id="p-gabung">
  <div class="w">
    <div class="jg">

      {{-- Kolom kiri: formulir & kontak --}}
      <div>
        <span class="tag mono">Bergabung</span>
        <h2 style="font-size:clamp(28px,3.4vw,40px);margin:12px 0">Siap mulai merintis usahamu?</h2>
        <p style="color:#475569">Terbuka untuk seluruh mahasiswa aktif, lintas jurusan dan angkatan. Tidak perlu sudah punya usaha — cukup niat belajar.</p>

        @if ($submitted)
          <div class="card" style="margin-top:20px;text-align:center">
            <h3>Pendaftaran terkirim</h3>
            <p style="color:#475569;margin:8px 0 18px">
              Terima kasih, {{ $name }}. Datamu sudah masuk ke tim rekrutmen HIPMI PT.
              Kamu juga bisa mengonfirmasi lewat WhatsApp resmi kami.
            </p>
            <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center">
              @if ($confirmUrl)
                <a class="btn p" target="_blank" rel="noopener noreferrer" href="{{ $confirmUrl }}">Konfirmasi via WhatsApp</a>
              @endif
              <button class="btn o" type="button" wire:click="resetForm">Daftarkan orang lain</button>
            </div>
          </div>
        @else
          <form class="fm" wire:submit="submitRegistration">
            <input wire:model.live="name" placeholder="Nama lengkap" autocomplete="name" maxlength="120">
            @error('name') <small style="color:#B91C1C">{{ $message }}</small> @enderror

            <input wire:model.live="study_program" placeholder="Program studi &amp; angkatan" maxlength="120">
            @error('study_program') <small style="color:#B91C1C">{{ $message }}</small> @enderror

            <input wire:model.live="whatsapp" type="tel" inputmode="tel" placeholder="Nomor WhatsApp (mis. 0812xxxxxxx)" maxlength="20">
            @error('whatsapp') <small style="color:#B91C1C">{{ $message }}</small> @enderror

            <textarea wire:model.live="motivation" rows="2" placeholder="Kenapa ingin bergabung?" maxlength="800"></textarea>
            @error('motivation') <small style="color:#B91C1C">{{ $message }}</small> @enderror

            <button class="btn p" style="justify-self:start" type="submit" wire:loading.attr="disabled" wire:target="submitRegistration">
              <span wire:loading.remove wire:target="submitRegistration">Kirim pendaftaran</span>
              <span wire:loading wire:target="submitRegistration">Mengirim…</span>
            </button>
          </form>
        @endif

        <div class="ct">
          <div>
            <small>EMAIL</small>
            <a href="mailto:{{ $email ?: 'info@hipmipt.org' }}">{{ $email ?: 'info@hipmipt.org' }}</a>
          </div>
          <div>
            <small>INSTAGRAM</small>
            @if ($instagram)
              <a href="https://instagram.com/{{ ltrim($instagram, '@') }}" target="_blank" rel="noopener noreferrer">{{ '@'.ltrim($instagram, '@') }}</a>
            @else
              @hipmipt
            @endif
          </div>
          <div>
            <small>SEKRETARIAT</small>
            {{ $secretariat ? preg_replace('/^Sekretariat\s*/i', '', $secretariat) : 'Gedung Kemahasiswaan, Lt. 2' }}
          </div>
        </div>
      </div>

      {{-- Kolom kanan: alur seleksi & FAQ --}}
      <div>
        <h3 style="margin-bottom:8px">Alur seleksi</h3>

        <div class="st">
          <span class="mono">01</span>
          <div><b>Formulir online</b><p>Isi data diri dan motivasi.</p></div>
        </div>
        <div class="st">
          <span class="mono">02</span>
          <div><b>Wawancara santai</b><p>Ngobrol dengan pengurus untuk saling mengenal.</p></div>
        </div>
        <div class="st">
          <span class="mono">03</span>
          <div><b>Business Bootcamp</b><p>Pelatihan dasar sebelum resmi menjadi anggota.</p></div>
        </div>

        <h3 style="margin:28px 0 4px">Pertanyaan umum</h3>

        <details>
          <summary>Apakah harus sudah punya usaha?</summary>
          <p>Tidak. Banyak anggota baru mulai dari nol di Business Bootcamp.</p>
        </details>
        <details>
          <summary>Berapa biaya iuran?</summary>
          <p>Hubungi narahubung lewat tombol WhatsApp untuk info iuran terbaru.</p>
        </details>
        <details>
          <summary>Bagaimana membagi waktu dengan kuliah?</summary>
          <p>Program dijadwalkan di luar jam kuliah dan bersifat fleksibel per divisi.</p>
        </details>
      </div>

    </div>
  </div>
</section>
