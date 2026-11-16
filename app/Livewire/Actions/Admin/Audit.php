<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Audit extends Component
{
    public string $search = '';
    public string $actionFilter = 'semua';
    public string $moduleFilter = 'semua';

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAccess('audit'), 403);
    }

    public function export()
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $logs = $this->filteredQuery()->latest()->limit(5000)->get();

        return response()->streamDownload(function () use ($logs) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Waktu', 'Aktor', 'Aksi', 'Modul', 'Keterangan']);

            foreach ($logs as $log) {
                fputcsv($out, [
                    $log->created_at?->format('Y-m-d H:i:s'),
                    $this->csvText($log->actor),
                    $this->csvText($log->action),
                    $this->csvText($log->module),
                    $this->csvText($log->description),
                ]);
            }

            fclose($out);
        }, 'audit-log-hipmi-'.now()->format('Ymd_His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function csvText($value): string
    {
        $text = (string) ($value ?? '');

        return preg_match('/^[=+\-@\t\r]/', $text) ? "'".$text : $text;
    }

    protected function filteredQuery()
    {
        return AuditLog::query()
            ->when($this->search !== '', fn ($query) => $query->where(function ($query) {
                $query->where('description', 'like', '%'.$this->search.'%')
                    ->orWhere('actor', 'like', '%'.$this->search.'%');
            }))
            ->when($this->actionFilter !== 'semua', fn ($query) => $query->where('action', $this->actionFilter))
            ->when($this->moduleFilter !== 'semua', fn ($query) => $query->where('module', $this->moduleFilter));
    }

    public function render()
    {
        $logs = $this->filteredQuery()
            ->latest()
            ->limit(300)
            ->get();

        return view('livewire.admin.audit', [
            'logs'     => $logs,
            'actions'  => AuditLog::query()->distinct()->orderBy('action')->pluck('action'),
            'modules'  => AuditLog::query()->distinct()->orderBy('module')->pluck('module'),
        ]);
    }
}
