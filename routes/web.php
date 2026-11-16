<?php

use App\Livewire\About;
use App\Livewire\Admin\Accounts;
use App\Livewire\Admin\Audit;
use App\Livewire\Admin\Businesses as AdminBusinesses;
use App\Livewire\Admin\ChangePassword;
use App\Livewire\Admin\Gallery as AdminGallery;
use App\Livewire\Admin\Info;
use App\Livewire\Admin\Login;
use App\Livewire\Admin\Overview;
use App\Livewire\Admin\Programs as AdminPrograms;
use App\Livewire\Admin\Registrations;
use App\Livewire\Admin\Settings;
use App\Livewire\Admin\Submissions;
use App\Livewire\Admin\Team as AdminTeam;
use App\Livewire\Businesses;
use App\Livewire\Divisions;
use App\Livewire\Gallery;
use App\Livewire\Home;
use App\Livewire\InformationPage;
use App\Livewire\Join;
use App\Livewire\Programs;
use App\Livewire\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman publik
|--------------------------------------------------------------------------
*/

Route::get('/', Home::class)->name('home');
Route::get('/tentang', About::class)->name('about');
Route::get('/bidang', Divisions::class)->name('divisions');
Route::get('/struktur', Team::class)->name('team');
Route::get('/program-kerja', Programs::class)->name('programs');
Route::get('/informasi', InformationPage::class)->name('information');
Route::get('/galeri', Gallery::class)->name('gallery');
Route::get('/bisnis-umkm', Businesses::class)->name('businesses');
Route::get('/bergabung', Join::class)->name('join');

/*
|--------------------------------------------------------------------------
| Autentikasi panel admin
|--------------------------------------------------------------------------
*/

// Middleware `auth` membutuhkan route bernama "login".
Route::redirect('/login', '/admin/login')->name('login');

Route::get('/admin/login', Login::class)->name('admin.login');

Route::post('/admin/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('home');
})->middleware('auth')->name('admin.logout');

/*
|--------------------------------------------------------------------------
| Panel admin (butuh login)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Overview::class)->name('overview');
    Route::get('/informasi', Info::class)->name('info');
    Route::get('/program-kerja', AdminPrograms::class)->name('programs');
    Route::get('/galeri', AdminGallery::class)->name('gallery');
    Route::get('/bisnis-umkm', AdminBusinesses::class)->name('businesses');
    Route::get('/pengajuan', Submissions::class)->name('submissions');
    Route::get('/pendaftaran', Registrations::class)->name('registrations');
    Route::get('/struktur', AdminTeam::class)->name('team');
    Route::get('/akun', Accounts::class)->name('accounts');
    Route::get('/audit', Audit::class)->name('audit');
    Route::get('/pengaturan', Settings::class)->name('settings');
    Route::get('/ganti-sandi', ChangePassword::class)->name('password');
});
