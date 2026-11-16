<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\GalleryAlbum;
use App\Models\GalleryPhoto;
use App\Support\Media;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class Gallery extends Component
{
    use WithFileUploads;

    public ?int $editingId = null;

    public string $title = '';
    public string $date_label = '';
    public string $category = 'Internal';
    public string $color = '#0D9488';
    public string $description = '';
    public bool $is_pinned = false;

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $photos = [];

    public string $search = '';

    public bool $panelOpen = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAccess('gallery'), 403);
    }

    protected function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:120'],
            'date_label'  => ['nullable', 'string', 'max:40'],
            'category'    => ['required', 'in:'.implode(',', GalleryAlbum::CATEGORIES)],
            'color'       => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:400'],
            'is_pinned'   => ['boolean'],
            'photos'      => ['array', 'max:10'],
            'photos.*'    => ['image', 'max:4096'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        $uploads = $this->photos;
        unset($data['photos']);

        if ($this->editingId) {
            $album = GalleryAlbum::findOrFail($this->editingId);
            $album->update($data);
            AuditLog::record('UPDATE', 'GALERI', 'memperbarui album: '.$album->title);
            session()->flash('status', 'Album berhasil diperbarui.');
        } else {
            $album = GalleryAlbum::create($data);
            AuditLog::record('CREATE', 'GALERI', 'menambah album: '.$album->title);
            session()->flash('status', 'Album berhasil ditambahkan.');
        }

        foreach ($uploads as $upload) {
            GalleryPhoto::create([
                'gallery_album_id' => $album->id,
                'path'             => $upload->store('gallery', 'public'),
                'sort_order'       => (int) GalleryPhoto::where('gallery_album_id', $album->id)->max('sort_order') + 1,
            ]);
        }

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $album = GalleryAlbum::findOrFail($id);

        $this->panelOpen = true;
        $this->editingId = $album->id;
        $this->title = $album->title;
        $this->date_label = (string) $album->date_label;
        $this->category = $album->category;
        $this->color = $album->color;
        $this->description = (string) $album->description;
        $this->is_pinned = (bool) $album->is_pinned;
        $this->photos = [];
        $this->resetValidation();
    }

    public function removePhoto(int $photoId): void
    {
        $photo = GalleryPhoto::findOrFail($photoId);
        $photo->delete();
        AuditLog::record('DELETE', 'GALERI', 'menghapus foto dari album #'.$photo->gallery_album_id);
        session()->flash('status', 'Foto dihapus.');
    }

    public function movePhoto(int $photoId, string $direction): void
    {
        $photo = GalleryPhoto::findOrFail($photoId);

        $photos = GalleryPhoto::where('gallery_album_id', $photo->gallery_album_id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->values();

        $index = $photos->search(fn ($item) => $item->id === $photo->id);

        if ($index === false) {
            return;
        }

        $target = $direction === 'up' ? $index - 1 : $index + 1;

        if ($target < 0 || $target >= $photos->count()) {
            return;
        }

        $items = $photos->all();
        [$items[$index], $items[$target]] = [$items[$target], $items[$index]];

        foreach ($items as $position => $item) {
            $item->update(['sort_order' => $position + 1]);
        }

        AuditLog::record('UPDATE', 'GALERI', 'mengubah urutan foto album #'.$photo->gallery_album_id);
        session()->flash('status', 'Urutan foto diperbarui.');
    }

    public function delete(int $id): void
    {
        $album = GalleryAlbum::findOrFail($id);
        $album->delete();
        AuditLog::record('DELETE', 'GALERI', 'menghapus album: '.$album->title);
        session()->flash('status', 'Album dihapus.');

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
            'editingId', 'title', 'date_label', 'description',
            'photos', 'is_pinned',
        ]);
        $this->panelOpen = false;
        $this->category = 'Internal';
        $this->color = '#0D9488';
        $this->resetValidation();
    }

    public function render()
    {
        $albums = GalleryAlbum::with('photos')
            ->when($this->search !== '', fn ($query) => $query->where('title', 'like', '%'.$this->search.'%'))
            ->ordered()
            ->get();

        $editing = $this->editingId
            ? GalleryAlbum::with('photos')->find($this->editingId)
            : null;

        return view('livewire.admin.gallery', [
            'albums'     => $albums,
            'editing'    => $editing,
            'total'      => GalleryAlbum::count(),
            'categories' => GalleryAlbum::CATEGORIES,
            'previews'   => collect($this->photos)->map(function ($photo) {
                try {
                    return $photo->temporaryUrl();
                } catch (\Throwable $e) {
                    return null;
                }
            })->filter()->values()->all(),
        ]);
    }
}
