<div class="adm-page">
<x-admin-page-header
  title="Manajemen Akun Admin"
  desc="Tambah akun pengurus, reset kata sandi, dan nonaktifkan akun pengurus yang demisioner."
  :link="route('home')"
/>

<x-admin-metrics />

<div class="adm-card">
  <div class="adm-card-h">
    <h3>Tambah akun pengurus</h3>
  </div>

  <form wire:submit="save">
    <div class="acf" style="margin-top:12px">
      <label class="adm-lb">Nama lengkap
        <input type="text" wire:model="name" maxlength="120" autocomplete="off">
        @error('name') <small style="color:#B91C1C">{{ $message }}</small> @enderror
      </label>
      <label class="adm-lb">Username
        <input type="text" wire:model="username" maxlength="30" autocapitalize="none" autocomplete="off" placeholder="mis. budi.santoso">
        @error('username') <small style="color:#B91C1C">{{ $message }}</small> @enderror
      </label>
      <label class="adm-lb">Email
        <input type="email" wire:model="email" maxlength="120" autocomplete="off">
        @error('email') <small style="color:#B91C1C">{{ $message }}</small> @enderror
      </label>
      <label class="adm-lb">Divisi
        <input type="text" wire:model="division" maxlength="80" placeholder="mis. Media &amp; Kominfo">
        @error('division') <small style="color:#B91C1C">{{ $message }}</small> @enderror
      </label>
      <label class="adm-lb">Role
        <select wire:model="role">
          @foreach ($roles as $key => $meta)
            <option value="{{ $key }}">{{ $meta['label'] }}</option>
          @endforeach
        </select>
      </label>
      <button class="btn p" type="submit" wire:loading.attr="disabled" wire:target="save">Buat akun</button>
    </div>
  </form>
</div>

<div class="adm-wrap" style="margin-top:16px">
  <table class="adm-tb">
    <thead>
      <tr>
        <th>Pengurus</th>
        <th>Username</th>
        <th>Divisi</th>
        <th>Role</th>
        <th>Status</th>
        <th>Login terakhir</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($users as $user)
        <tr wire:key="user-{{ $user->id }}">
          <td>
            <b>{{ $user->name }}</b>
            <br><small class="mono" style="color:var(--mut)">{{ $user->email }}</small>
          </td>
          <td class="mono">{{ $user->username }}</td>
          <td>{{ $user->division }}</td>
          <td><span class="tag mono" style="--c:{{ $user->roleColor() }}">{{ $user->roleLabel() }}</span></td>
          <td><span class="adm-pill {{ $user->is_active ? 'ok' : '' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
          <td class="mono">{{ $user->last_login_at?->format('d M Y H:i') ?? '—' }}</td>
          <td>
            <div class="adm-btns">
              <button class="xs" type="button" wire:click="resetPassword({{ $user->id }})"
                      wire:confirm="Reset kata sandi akun {{ $user->username }}?">Reset Password</button>
              <button class="xs" type="button" wire:click="toggleActive({{ $user->id }})">{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
              <button class="xs" type="button" wire:click="delete({{ $user->id }})"
                      wire:confirm="Hapus akun {{ $user->username }}? Riwayat aktivitasnya tetap tersimpan di Audit Log.">Hapus</button>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="empty">Belum ada akun admin.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
</div>
