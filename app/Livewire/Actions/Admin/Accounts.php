<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Accounts extends Component
{
    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $division = '';
    public string $role = 'admin_media';

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAccess('accounts'), 403);
    }

    protected function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'regex:/^[a-z0-9_.]{3,30}$/', Rule::unique('users', 'username')],
            'email'    => ['required', 'email', 'max:120', Rule::unique('users', 'email')],
            'division' => ['required', 'string', 'max:80'],
            'role'     => ['required', 'in:'.implode(',', array_keys(User::ROLES))],
        ];
    }

    protected function messages(): array
    {
        return [
            'username.regex' => 'Username 3–30 karakter: huruf kecil, angka, titik, atau garis bawah.',
        ];
    }

    public function save(): void
    {
        $this->name = trim($this->name);
        $this->username = Str::lower(trim($this->username));
        $this->email = trim($this->email);

        $data = $this->validate();

        $password = Str::random(10);

        $user = User::create($data + [
            'password'             => $password,
            'is_active'            => true,
            'must_change_password' => true,
        ]);

        AuditLog::record('CREATE', 'AKUN', 'membuat akun pengurus: '.$user->username.' ('.$user->roleLabel().')');

        $this->dispatch(
            'admin-credential',
            title: 'Akun pengurus dibuat',
            username: $user->username,
            password: $password,
        );

        session()->flash('status', 'Akun berhasil dibuat.');
        $this->resetForm();
    }

    public function toggleActive(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->is_active && (
            $user->id === auth()->id()
            || ($user->role === 'super_admin' && User::where('role', 'super_admin')->where('is_active', true)->count() < 2)
        )) {
            session()->flash('status', 'Tidak dapat menonaktifkan akun sendiri atau Super Admin aktif terakhir.');

            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
        AuditLog::record('UPDATE', 'AKUN', ($user->is_active ? 'mengaktifkan' : 'menonaktifkan').' akun: '.$user->username);
        session()->flash('status', 'Status akun diperbarui.');
    }

    public function resetPassword(int $id): void
    {
        $user = User::findOrFail($id);

        $temporary = Str::random(10);

        $user->update([
            'password'             => $temporary,
            'must_change_password' => true,
        ]);

        AuditLog::record('UPDATE', 'AKUN', 'mereset kata sandi akun: '.$user->username);

        $this->dispatch(
            'admin-credential',
            title: 'Kata sandi sementara',
            username: $user->username,
            password: $temporary,
        );

        session()->flash('status', 'Kata sandi sementara untuk '.$user->username.' sudah dibuat.');
    }

    public function delete(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()
            || ($user->role === 'super_admin' && User::where('role', 'super_admin')->count() < 2)
        ) {
            session()->flash('status', 'Tidak dapat menghapus akun sendiri atau Super Admin terakhir. Gunakan Nonaktifkan.');

            return;
        }

        $user->delete();
        AuditLog::record('DELETE', 'AKUN', 'menghapus akun: '.$user->username);
        session()->flash('status', 'Akun dihapus.');
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'username', 'email', 'division']);
        $this->role = 'admin_media';
        $this->resetValidation();
    }

    public function render()
    {
        $users = User::query()->orderBy('name')->get();

        return view('livewire.admin.accounts', [
            'users' => $users,
            'roles' => User::ROLES,
        ]);
    }
}
