<?php

namespace App\Livewire;

use App\Models\Information;
use App\Models\Setting;
use App\Support\Media;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class InformationPage extends Component
{
    public string $filter = 'Semua';

    public function render()
    {
        $all = Information::visible()->orderByDesc('is_pinned')->latest()->get();

        $items = $this->filter === 'Semua'
            ? $all
            : $all->where('category', $this->filter)->values();

        $wa = preg_replace('/\D/', '', (string) Setting::get('whatsapp', ''));
        $waNumber = str_starts_with($wa, '0') ? '62'.substr($wa, 1) : (str_starts_with($wa, '8') ? '62'.$wa : $wa);

        $payload = $all->map(fn (Information $info) => [
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

        return view('livewire.information-page', [
            'items'    => $items,
            'payload'  => $payload,
            'waNumber' => $waNumber,
        ]);
    }
}
