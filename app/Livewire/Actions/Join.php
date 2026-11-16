<?php

namespace App\Livewire;

use App\Models\Registration;
use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Join extends Component
{
    public string $name = '';
    public string $study_program = '';
    public string $whatsapp = '';
    public string $motivation = '';

    public bool $submitted = false;
    public string $confirmUrl = '';

    public function submitRegistration(): void
    {
        $data = $this->validate([
            'name'          => ['required', 'string', 'max:120'],
            'study_program' => ['nullable', 'string', 'max:120'],
            'whatsapp'      => ['required', 'string', 'min:10', 'max:20'],
            'motivation'    => ['nullable', 'string', 'max:800'],
        ], [], [
            'name'     => 'nama lengkap',
            'whatsapp' => 'nomor WhatsApp',
        ]);

        $digits = preg_replace('/\D/', '', $data['whatsapp']);
        $wa = str_starts_with($digits, '0')
            ? '62'.substr($digits, 1)
            : (str_starts_with($digits, '8') ? '62'.$digits : $digits);

        Registration::create([
            'name'          => $data['name'],
            'study_program' => $data['study_program'] ?? '',
            'whatsapp'      => $digits,
            'motivation'    => $data['motivation'] ?? '',
            'status'        => 'baru',
        ]);

        $official = preg_replace('/\D/', '', (string) Setting::get('whatsapp', ''));
        $official = str_starts_with($official, '0')
            ? '62'.substr($official, 1)
            : (str_starts_with($official, '8') ? '62'.$official : $official);

        $text = "Halo HIPMI PT, saya {$data['name']} baru saja mendaftar.\n"
            .'Prodi/Angkatan: '.($data['study_program'] ?? '')."\n"
            .'Motivasi: '.($data['motivation'] ?? '');

        $this->confirmUrl = $official
            ? 'https://wa.me/'.$official.'?text='.urlencode($text)
            : '';

        $this->submitted = true;
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'study_program', 'whatsapp', 'motivation', 'submitted', 'confirmUrl']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.join', [
            'email'       => Setting::get('email'),
            'instagram'   => Setting::get('instagram'),
            'secretariat' => Setting::get('secretariat'),
        ]);
    }
}
