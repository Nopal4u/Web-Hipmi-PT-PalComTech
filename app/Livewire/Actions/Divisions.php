<?php

namespace App\Livewire;

use App\Models\Division;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Divisions extends Component
{
    public function render()
    {
        $divisions = Division::with(['members', 'programs'])
            ->ordered()
            ->get();

        return view('livewire.divisions', compact('divisions'));
    }
}
