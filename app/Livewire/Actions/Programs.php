<?php

namespace App\Livewire;

use App\Models\Division;
use App\Models\Program;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Programs extends Component
{
    public string $filter = 'all';

    public function render()
    {
        $divisions = Division::ordered()->get();

        $programs = Program::with('division')
            ->when($this->filter !== 'all', fn ($query) => $query->where('division_id', (int) $this->filter))
            ->orderByDesc('id')
            ->get();

        return view('livewire.programs', compact('divisions', 'programs'));
    }
}
