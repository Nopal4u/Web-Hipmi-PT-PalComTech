<div class="adm-page">
<x-admin-page-header
  title="Ganti Kata Sandi"
  desc="Demi keamanan, gunakan kata sandi yang kuat dan tidak dipakai di layanan lain."
/>

@if (auth()->user()?->must_change_password)
  <div class="adm-bar" style="border-color:#FDE68A;background:#FFFBEB;color:#B45309;font-weight:600;font-size:13px">
    Anda masih menggunakan kata sandi default. Silakan ganti sekarang sebelum melanjutkan.
  </div>
@endif

<div class="adm-cols">
  <div class="adm-card">
    <div class="adm-card-h">
      <h3>Perbarui kata sandi</h3>
    </div>

    <form wire:submit="save">
      <div class="adm-fs">
        <label class="adm-lb">Kata sandi saat ini *
          <input type="password" wire:model="current_password" autocomplete="current-password">
          @error('current_password') <small style="color:#B91C1C">{{ $message }}</small> @enderror
        </label>

        <label class="adm-lb">Kata sandi baru *
          <input type="password" wire:model="password" autocomplete="new-password">
          @error('password') <small style="color:#B91C1C">{{ $message }}</small> @enderror
        </label>

        <label class="adm-lb">Ulangi kata sandi baru *
          <input type="password" wire:model="password_confirmation" autocomplete="new-password">
        </label>
      </div>

      <div class="adm-foot" style="margin-top:18px">
        <button class="btn p" type="submit">Simpan kata sandi</button>
      </div>
    </form>
  </div>

  <div class="adm-card">
    <div class="adm-card-h">
      <h3>Tips keamanan</h3>
    </div>
    <ul style="margin:12px 0 0;padding-left:20px;font-size:14px;color:#475569;line-height:1.9">
      <li>Minimal 8 karakter, disarankan kombinasi huruf, angka, dan simbol.</li>
      <li>Jangan bagikan kata sandi kepada pengurus lain.</li>
      <li>Ganti kata sandi default <b>hipmi2026</b> segera setelah login pertama.</li>
      <li>Gunakan tombol keluar setelah selesai memakai perangkat bersama.</li>
    </ul>
  </div>
</div>
</div>
