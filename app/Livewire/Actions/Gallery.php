<?php

namespace App\Livewire;

use App\Models\GalleryAlbum;
use App\Support\Media;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Gallery extends Component
{
    public string $filter = 'Semua';

    public function render()
    {
        $all = GalleryAlbum::with('photos')->ordered()->get();

        $albums = $this->filter === 'Semua'
            ? $all
            : $all->where('category', $this->filter)->values();

        $payload = $all->map(fn (GalleryAlbum $album) => [
            'id'          => $album->id,
            'title'       => $album->title,
            'date'        => $album->date_label,
            'category'    => $album->category,
            'color'       => $album->color,
            'description' => $album->description,
            'photos'      => $album->photos->map(fn ($photo) => Media::url($photo->path))->values(),
        ])->values();

        return view('livewire.gallery', [
            'albums'  => $albums,
            'payload' => $payload,
        ]);
    }
}
