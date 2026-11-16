<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessSubmission;
use App\Support\Media;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Businesses extends Component
{
    use WithFileUploads;

    public bool $submitted = false;

    public string $founder = '';
    public string $study_program = '';
    public string $year = '';
    public string $nim = '';
    public string $brand = '';
    public string $category = 'F&B';
    public string $description = '';
    public string $whatsapp = '';
    public string $instagram = '';

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $photos = [];

    /** Validasi per langkah wizard (dipanggil dari Alpine sebelum pindah step). */
    public function validateStep(int $step): bool
    {
        $rules = match ($step) {
            1 => [
                'founder'       => ['required', 'string', 'max:120'],
                'study_program' => ['required', 'string', 'max:60'],
                'year'          => ['required', 'digits:4'],
                'nim'           => ['required', 'string', 'max:30'],
            ],
            2 => [
                'brand'       => ['required', 'string', 'max:100'],
                'category'    => ['required', 'in:'.implode(',', array_keys(Business::CATEGORIES))],
                'description' => ['required', 'string', 'max:400'],
            ],
            3 => [
                'whatsapp'  => ['required', 'string', 'min:10', 'max:20'],
                'instagram' => ['nullable', 'string', 'max:40'],
                'photos'    => ['array', 'max:5'],
                'photos.*'  => ['image', 'max:4096'],
            ],
            default => [],
        };

        if ($rules === []) {
            return true;
        }

        try {
            $this->validate($rules, [], [
                'founder'       => 'nama founder',
                'study_program' => 'program studi',
                'year'          => 'angkatan',
                'brand'         => 'nama brand',
                'description'   => 'deskripsi usaha',
                'whatsapp'      => 'nomor WhatsApp',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return false;
        }

        return true;
    }

    public function submitBusiness(): void
    {
        $data = $this->validate([
            'founder'       => ['required', 'string', 'max:120'],
            'study_program' => ['required', 'string', 'max:60'],
            'year'          => ['required', 'digits:4'],
            'nim'           => ['required', 'string', 'max:30'],
            'brand'         => ['required', 'string', 'max:100'],
            'category'      => ['required', 'in:'.implode(',', array_keys(Business::CATEGORIES))],
            'description'   => ['required', 'string', 'max:400'],
            'whatsapp'      => ['required', 'string', 'min:10', 'max:20'],
            'instagram'     => ['nullable', 'string', 'max:40'],
            'photos'        => ['array', 'max:5'],
            'photos.*'      => ['image', 'max:4096'],
        ], [], [
            'founder'       => 'nama founder',
            'study_program' => 'program studi',
            'year'          => 'angkatan',
            'brand'         => 'nama brand',
            'description'   => 'deskripsi usaha',
            'whatsapp'      => 'nomor WhatsApp',
        ]);

        $paths = [];
        foreach ($this->photos as $photo) {
            $paths[] = $photo->store('submissions', 'public');
        }

        $submission = BusinessSubmission::create([
            'brand'         => $data['brand'],
            'category'      => $data['category'],
            'description'   => $data['description'],
            'founder'       => $data['founder'],
            'study_program' => $data['study_program'],
            'year'          => $data['year'],
            'nim'           => $data['nim'],
            'whatsapp'      => preg_replace('/\D/', '', $data['whatsapp']),
            'instagram'     => ltrim($data['instagram'] ?? '', '@'),
            'status'        => 'menunggu',
            'photos'        => $paths,
        ]);

        AuditLog::record('CREATE', 'PENGAJUAN', 'pengajuan usaha baru: '.$submission->brand);

        $this->submitted = true;
    }

    public function resetSubmission(): void
    {
        $this->reset([
            'submitted', 'founder', 'study_program', 'year', 'nim',
            'brand', 'description', 'whatsapp', 'instagram', 'photos',
        ]);
        $this->category = 'F&B';
        $this->resetValidation();
    }

    public function removePhoto(int $index): void
    {
        unset($this->photos[$index]);
        $this->photos = array_values($this->photos);
    }

    public function render()
    {
        $businesses = Business::with('photos')->active()->ordered()->get();

        $payload = $businesses->map(fn (Business $business) => [
            'id'          => $business->id,
            'name'        => $business->name,
            'category'    => $business->category,
            'color'       => $business->color,
            'founder'     => $business->founder,
            'description' => $business->description,
            'instagram'   => $business->instagram,
            'instagram_url' => $business->instagram
                ? 'https://instagram.com/'.ltrim($business->instagram, '@')
                : '',
            'wa_link'     => $business->waLink(),
            'logo'        => Media::url($business->logo),
            'initial'     => $business->initial(),
            'photos'      => $business->photos->map(fn ($photo) => Media::url($photo->path))->values(),
        ])->values();

        return view('livewire.businesses', [
            'businesses' => $businesses,
            'payload'    => $payload,
            'categories' => Business::CATEGORIES,
        ]);
    }
}
