<?php

namespace App\Livewire;

use App\Models\GalleryAlbum;
use App\Models\Information;
use App\Models\Setting;
use App\Support\Media;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Home extends Component
{
    public string $filter = 'all';

    public function render()
    {
        $albums = GalleryAlbum::with('photos')->ordered()->get();
        $information = Information::visible()->orderByDesc('is_pinned')->latest()->get();

        $items = collect();

        if ($this->filter !== 'info') {
            foreach ($albums as $album) {
                $items->push([
                    'type'           => 'dok',
                    'id'             => $album->id,
                    'title'          => $album->title,
                    'date'           => $album->date_label,
                    'summary'        => $album->description,
                    'cover'          => Media::url($album->photos->first()?->path),
                    'count'          => $album->photos->count(),
                    'status'         => null,
                    'pinned'         => $album->is_pinned ? 1 : 0,
                    'updated'        => $album->updated_at?->timestamp ?? 0,
                    'color'          => '#0D9488',
                ]);
            }
        }

        if ($this->filter !== 'dok') {
            foreach ($information as $info) {
                $items->push([
                    'type'           => 'info',
                    'id'             => $info->id,
                    'title'          => $info->title,
                    'date'           => $info->date_label,
                    'summary'        => $info->summary,
                    'cover'          => Media::url($info->banner),
                    'count'          => 0,
                    'status'         => $info->status,
                    'pinned'         => $info->is_pinned ? 1 : 0,
                    'updated'        => $info->updated_at?->timestamp ?? 0,
                    'color'          => '#EA580C',
                ]);
            }
        }

        $items = $items
            ->sortBy([['pinned', 'desc'], ['updated', 'desc']])
            ->take(4)
            ->values();

        $wa = preg_replace('/\D/', '', (string) Setting::get('whatsapp', ''));
        $waNumber = str_starts_with($wa, '0') ? '62'.substr($wa, 1) : (str_starts_with($wa, '8') ? '62'.$wa : $wa);

        $albumPayload = $albums->map(fn (GalleryAlbum $album) => [
            'id'          => $album->id,
            'title'       => $album->title,
            'date'        => $album->date_label,
            'category'    => $album->category,
            'color'       => $album->color,
            'description' => $album->description,
            'photos'      => $album->photos->map(fn ($photo) => Media::url($photo->path))->values(),
        ])->values();

        $infoPayload = $information->map(fn (Information $info) => [
            'id'             => $info->id,
            'title'          => $info->title,
            'category'       => $info->category,
            'category_color' => $info->categoryColor(),
            'date'           => $info->date_label,
            'status_label'   => $info->statusLabel(),
            'status_color'   => $info->statusColor(),
            'summary'        => $info->summary,
            'description'    => $info->description,
            'action_label'   => $info->action_label,
            'action_url'     => $info->action_url,
            'locked'         => $info->isLocked(),
            'wa_url'         => $waNumber
                ? 'https://wa.me/'.$waNumber.'?text='.urlencode('Halo HIPMI PT, saya ingin bertanya tentang: '.$info->title)
                : '',
            'banner'         => Media::url($info->banner),
        ])->values();

        $ticker = json_decode((string) Setting::get('ticker', '[]'), true);
        if (! is_array($ticker) || $ticker === []) {
            $ticker = [
                'GALERI KEGIATAN & KATALOG USAHA MAHASISWA',
                'SEMINAR KEWIRAUSAHAAN SETIAP BULAN',
                'BAZAR KAMPUS & SHOWCASE USAHA ANGGOTA',
                'MENTORING BERSAMA PENGUSAHA SENIOR BPC HIPMI',
                'BUSINESS MATCHING BERSAMA MITRA UMKM',
            ];
        }

        return view('livewire.home', [
            'albums'        => $albums,
            'information'   => $information,
            'items'         => $items,
            'ticker'        => $ticker,
            'waNumber'      => $waNumber,
            'albumPayload'  => $albumPayload,
            'infoPayload'   => $infoPayload,
        ]);
    }
}
