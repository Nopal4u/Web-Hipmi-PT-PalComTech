<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Division;
use App\Models\Program;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Programs extends Component
{
    public ?int $editingId = null;

    public ?int $division_id = null;
    public string $title = '';
    public string $description = '';
    public string $schedule = '';
    public string $audience = '';
    public string $status = 'mendatang';

    public string $search = '';

    public bool $panelOpen = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAccess('programs'), 403);

        $this->division_id = Division::ordered()->value('id');
    }

    protected function rules(): array
    {
        return [
            'division_id' => ['required', 'exists:divisions,id'],
            'title'       => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:600'],
            'schedule'    => ['nullable', 'string', 'max:60'],
            'audience'    => ['nullable', 'string', 'max:100'],
            'status'      => ['required', 'in:'.implode(',', array_keys(Program::STATUSES))],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingId) {
            $program = Program::findOrFail($this->editingId);
            $program->update($data);
            AuditLog::record('UPDATE', 'PROKER', 'memperbarui program kerja: '.$program->title);
            session()->flash('status', 'Program kerja berhasil diperbarui.');
        } else {
            $program = Program::create($data);
            AuditLog::record('CREATE', 'PROKER', 'menambah program kerja: '.$program->title);
            session()->flash('status', 'Program kerja berhasil ditambahkan.');
        }

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $program = Program::findOrFail($id);

        $this->panelOpen = true;
        $this->editingId = $program->id;
        $this->division_id = $program->division_id;
        $this->title = $program->title;
        $this->description = (string) $program->description;
        $this->schedule = (string) $program->schedule;
        $this->audience = (string) $program->audience;
        $this->status = $program->status;
        $this->resetValidation();
    }

    public function cycleStatus(int $id): void
    {
        $program = Program::findOrFail($id);
        $order = array_keys(Program::STATUSES);
        $index = array_search($program->status, $order, true);
        $next = $order[($index === false ? 0 : $index + 1) % count($order)];

        $program->update(['status' => $next]);
        AuditLog::record('UPDATE', 'PROKER', 'mengubah status program: '.$program->title.' → '.$program->statusLabel());
        session()->flash('status', 'Status program diperbarui.');
    }

    public function delete(int $id): void
    {
        $program = Program::findOrFail($id);
        $program->delete();
        AuditLog::record('DELETE', 'PROKER', 'menghapus program kerja: '.$program->title);
        session()->flash('status', 'Program kerja dihapus.');

        if ($this->editingId === $id) {
            $this->resetForm();
        }
    }

    public function duplicate(int $id): void
    {
        $program = Program::findOrFail($id);

        $copy = $program->replicate();
        $copy->title = $program->title.' (salinan)';
        $copy->save();

        AuditLog::record('CREATE', 'PROKER', 'menduplikasi program kerja: '.$program->title);
        session()->flash('status', 'Program kerja berhasil diduplikasi.');
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
        $this->reset(['editingId', 'division_id', 'title', 'description', 'schedule', 'audience']);
        $this->panelOpen = false;
        $this->division_id = Division::ordered()->value('id');
        $this->status = 'mendatang';
        $this->resetValidation();
    }

    public function render()
    {
        $divisions = Division::ordered()->get();

        $programs = Program::with('division')
            ->when($this->search !== '', fn ($query) => $query->where('title', 'like', '%'.$this->search.'%'))
            ->orderByDesc('id')
            ->get();

        return view('livewire.admin.programs', [
            'divisions' => $divisions,
            'programs'  => $programs,
            'total'     => Program::count(),
            'statuses'  => Program::STATUSES,
        ]);
    }
}
