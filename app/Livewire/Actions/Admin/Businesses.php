<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessPhoto;
use App\Support\Media;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class Businesses extends Component
{
    use WithFileUploads;

    public ?int $editingId = null;

    public string $name = '';
    public string $category = 'F&B';
    public string $color = '#10B981';
    public string $founder = '';
    public string $description = '';
    public string $instagram = '';
    public string $whatsapp = '';
    public bool $is_active = true;

    public $logo = null;

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $photos = [];

    public string $search = '';
    public string $categoryFilter = 'semua';

    public bool $panelOpen = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAccess('businesses'), 403);
    }

    protected function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:100'],
            'category'      => ['required', 'in:'.implode(',', array_keys(Business::CATEGORIES))],
            'color'         => ['required', 'string', 'max:20'],
            'founder'       => ['required', 'string', 'max:120'],
            'description'   => ['required', 'string', 'max:400'],
            'instagram'     => ['nullable', 'string', 'max:40'],
            'whatsapp'      => ['required', 'string', 'min:10', 'max:20'],
            'is_active'     => ['boolean'],
            'logo'          => ['nullable', 'image', 'max:2048'],
            'photos'        => ['array', 'max:5'],
            'photos.*'      => ['image', 'max:4096'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        $uploads = $this->photos;
        unset($data['photos'], $data['logo']);

        $data['whatsapp'] = preg_replace('/\D/', '', $data['whatsapp']);
        $data['instagram'] = ltrim((string) ($data['instagram'] ?? ''), '@');

        if ($this->logo) {
            $data['logo'] = $this->logo->store('businesses', 'public');
        }

        if ($this->editingId) {
            $business = Business::findOrFail($this->editingId);
            $business->update($data);
            AuditLog::record('UPDATE', 'UMKM', 'memperbarui usaha: '.$business->name);
            session()->flash('status', 'Usaha berhasil diperbarui.');
        } else {
            $business = Business::create($data);
            AuditLog::record('CREATE', 'UMKM', 'menambah usaha: '.$business->name);
            session()->flash('status', 'Usaha berhasil ditambahkan.');
        }

        foreach ($uploads as $upload) {
            BusinessPhoto::create([
                'business_id' => $business->id,
                'path'        => $upload->store('businesses', 'public'),
                'sort_order'  => (int) BusinessPhoto::where('business_id', $business->id)->max('sort_order') + 1,
            ]);
        }

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $business = Business::findOrFail($id);

        $this->panelOpen = true;
        $this->editingId = $business->id;
        $this->name = $business->name;
        $this->category = $business->category;
        $this->color = $business->color;
        $this->founder = $business->founder;
        $this->description = $business->description;
        $this->instagram = (string) $business->instagram;
        $this->whatsapp = (string) $business->whatsapp;
        $this->is_active = (bool) $business->is_active;
        $this->logo = null;
        $this->photos = [];
        $this->resetValidation();
    }

    public function toggleActive(int $id): void
    {
        $business = Business::findOrFail($id);
        $business->update(['is_active' => ! $business->is_active]);
        AuditLog::record('UPDATE', 'UMKM', ($business->is_active ? 'mengaktifkan' : 'menonaktifkan').' usaha: '.$business->name);
        session()->flash('status', 'Status tampil usaha diperbarui.');
    }

    public function removePhoto(int $photoId): void
    {
        $photo = BusinessPhoto::findOrFail($photoId);
        $photo->delete();
        AuditLog::record('DELETE', 'UMKM', 'menghapus foto usaha #'.$photo->business_id);
        session()->flash('status', 'Foto dihapus.');
    }

    public function delete(int $id): void
    {
        $business = Business::findOrFail($id);
        $business->delete();
        AuditLog::record('DELETE', 'UMKM', 'menghapus usaha: '.$business->name);
        session()->flash('status', 'Usaha dihapus.');

        if ($this->editingId === $id) {
            $this->resetForm();
        }
    }

    public function openPanel(): void
    {
        $this->resetForm();
        $this->panelOpen = true;
    }

    public function closePanel(): void
    {
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId', 'name', 'founder', 'description', 'instagram',
            'whatsapp', 'logo', 'photos',
        ]);
        $this->panelOpen = false;
        $this->category = 'F&B';
        $this->color = '#10B981';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $businesses = Business::with('photos')
            ->when($this->search !== '', fn ($query) => $query->where(function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('founder', 'like', '%'.$this->search.'%');
            }))
            ->when($this->categoryFilter !== 'semua', fn ($query) => $query->where('category', $this->categoryFilter))
            ->ordered()
            ->get();

        $editing = $this->editingId
            ? Business::with('photos')->find($this->editingId)
            : null;

        $previews = collect($this->photos)->map(function ($photo) {
            try {
                return $photo->temporaryUrl();
            } catch (\Throwable $e) {
                return null;
            }
        })->filter()->values()->all();

        $logoUrl = null;
        if ($this->logo) {
            try {
                $logoUrl = $this->logo->temporaryUrl();
            } catch (\Throwable $e) {
                $logoUrl = null;
            }
        } elseif ($editing?->logo) {
            $logoUrl = Media::url($editing->logo);
        }

        return view('livewire.admin.businesses', [
            'businesses' => $businesses,
            'editing'    => $editing,
            'total'      => Business::count(),
            'categories' => Business::CATEGORIES,
            'logoUrl'    => $logoUrl,
            'previews'   => $previews,
        ]);
    }
}
