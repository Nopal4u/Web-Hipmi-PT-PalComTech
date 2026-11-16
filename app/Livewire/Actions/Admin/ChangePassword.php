<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class ChangePassword extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAccess('password'), 403);
    }

    public function save(): void
    {
        $this->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'max:120', 'confirmed'],
        ], [], [
            'current_password' => 'kata sandi saat ini',
            'password'         => 'kata sandi baru',
        ]);

        $user = auth()->user();

        if (! Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'Kata sandi saat ini tidak cocok.');

            return;
        }

        $user->update([
            'password'             => $this->password,
            'must_change_password' => false,
        ]);

        AuditLog::record('UPDATE', 'AKUN', 'mengganti kata sandi sendiri');

        $this->reset(['current_password', 'password', 'password_confirmation']);

        session()->flash('status', 'Kata sandi berhasil diperbarui.');
        $this->redirectRoute('admin.overview', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.change-password');
    }
}
