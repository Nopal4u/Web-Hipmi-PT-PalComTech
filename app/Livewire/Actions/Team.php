<?php

namespace App\Livewire;

use App\Models\Division;
use App\Models\Member;
use App\Support\Media;
use App\Support\Url;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Team extends Component
{
    public string $filter = 'all';

    public function render()
    {
        $members = Member::with('division')->ordered()->get();
        $divisions = Division::ordered()->get();

        $tabs = collect([
            [
                'key'   => 'all',
                'label' => 'Semua Pengurus',
                'count' => $members->where('group', '!=', 'dosen')->count(),
            ],
            [
                'key'   => 'bph',
                'label' => 'BPH Inti',
                'count' => $members->where('group', 'bph')->count(),
            ],
        ]);

        foreach ($divisions as $division) {
            $tabs->push([
                'key'   => 'd'.$division->id,
                'label' => 'Bidang '.$division->numeral.': '.$division->short_name,
                'count' => $members->where('division_id', $division->id)->count(),
            ]);
        }

        $visible = match (true) {
            $this->filter === 'all'  => $members,
            $this->filter === 'bph'  => $members->where('group', 'bph')->values(),
            str_starts_with($this->filter, 'd') => $members
                ->where('division_id', (int) substr($this->filter, 1))
                ->values(),
            default => $members,
        };

        $payload = $members->map(fn (Member $member) => [
            'id'             => $member->id,
            'name'           => $member->name,
            'position'       => $member->position,
            'badge'          => $member->badgeLabel(),
            'color'          => $member->accent(),
            'photo'          => Media::url($member->photo),
            'initial'        => $member->initial(),
            'division'       => $member->group === 'dosen'
                ? 'Pembina organisasi'
                : ($member->group === 'bph'
                    ? 'Pengurus Inti (BPH)'
                    : ($member->division?->name ?? 'Pengurus')),
            'expertise'      => $member->expertise,
            'study_program'  => $member->study_program,
            'year'           => $member->shortYear(),
            'faculty'        => $member->faculty,
            'nidn'           => $member->nidn,
            'business'       => $member->business,
            'cv_url'         => Url::external($member->cv_url),
            'portfolio_url'  => Url::external($member->portfolio_url),
            'linkedin'       => Url::external($member->linkedin),
            'instagram'      => $member->instagram,
            'profile_type'   => $member->profile_type,
        ])->values();

        return view('livewire.team', compact('members', 'divisions', 'tabs', 'visible', 'payload'));
    }
}
