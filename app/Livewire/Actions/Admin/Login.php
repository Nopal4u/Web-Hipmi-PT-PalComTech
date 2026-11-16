<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin-guest')]
class Login extends Component
{
    public string $username = '';
    public string $password = '';
    public bool $remember = false;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirectRoute('admin.overview', navigate: true);
        }
    }

    public function login(): void
    {
        $this->validate([
            'username' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string'],
        ], [], [
            'username' => 'username / email',
            'password' => 'kata sandi',
        ]);

        $key = 'admin-login:'.strtolower($this->username).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'username' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        $user = User::query()
            ->where('username', $this->username)
            ->orWhere('email', $this->username)
            ->first();

        if (! $user || ! Auth::attempt(
            ['username' => $user->username, 'password' => $this->password],
            $this->remember
        )) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'username' => 'Username atau kata sandi tidak cocok.',
            ]);
        }

        if (! $user->is_active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'username' => 'Akun ini dinonaktifkan. Hubungi Super Admin.',
            ]);
        }

        RateLimiter::clear($key);
        request()->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();

        AuditLog::record('LOGIN', 'AUTH', 'masuk ke panel admin', $user);

        if ($user->must_change_password) {
            $this->redirectRoute('admin.password', navigate: true);

            return;
        }

        $this->redirectIntended(route('admin.overview'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.login');
    }
}
