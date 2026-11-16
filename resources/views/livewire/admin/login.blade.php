<div class="ag" style="margin-top:80px">
  <div class="ag-logo">
    <img src="{{ asset('images/hipmi.webp') }}" alt="Logo HIPMI" style="width:40px;height:40px;object-fit:contain">
    <span style="font-weight:800;font-size:15px;letter-spacing:.02em">HIPMI ADMIN<small style="display:block;font-weight:500;font-size:11px;color:#64748B">PT PalComTech</small></span>
  </div>

  <h3>Masuk ke panel admin</h3>
  <p>Masuk dengan username dan kata sandi pengurus Anda.</p>

  <form wire:submit="login" style="display:grid;gap:14px">
    <label class="adm-lb" for="aun">Username / Email pengurus</label>
    <span class="ag-in">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      <input id="aun" wire:model="username" type="text" autocomplete="username" placeholder="mis. admin_kominfo">
    </span>

    <label class="adm-lb" for="apw">Kata sandi</label>
    <span class="ag-in">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
      <input id="apw" wire:model="password" type="password" autocomplete="current-password" placeholder="Kata sandi">
    </span>

    @error('username')
      <div id="aerr" role="alert" style="margin:0">{{ $message }}</div>
    @enderror
    @error('password')
      <div id="aerr" role="alert" style="margin:0">{{ $message }}</div>
    @enderror

    <label class="adm-chk" style="font-size:13px;font-weight:500">
      <input type="checkbox" wire:model="remember"> Ingat saya di perangkat ini
    </label>

    <button class="btn p" type="submit" wire:loading.attr="disabled" wire:target="login">
      <span wire:loading.remove wire:target="login">Masuk</span>
      <span wire:loading wire:target="login">Memeriksa…</span>
    </button>
  </form>

  <a class="ag-back" href="{{ route('home') }}">Kembali ke web publik</a>
</div>
