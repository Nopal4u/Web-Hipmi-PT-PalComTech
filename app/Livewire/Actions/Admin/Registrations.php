<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Registration;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Registrations extends Component
{
    public string $search = '';
    public string $statusFilter = 'semua';

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAccess('registrations'), 403);
    }

    public function updateStatus(int $id, string $status): void
    {
        abort_unless(array_key_exists($status, Registration::STATUSES), 422);

        $registration = Registration::findOrFail($id);
        $registration->update(['status' => $status]);

        AuditLog::record('UPDATE', 'PENDAFTARAN', 'mengubah status pendaftar '.$registration->name.' → '.$registration->statusLabel());
        session()->flash('status', 'Status pendaftar diperbarui.');
    }

    public function delete(int $id): void
    {
        $registration = Registration::findOrFail($id);
        $registration->delete();
        AuditLog::record('DELETE', 'PENDAFTARAN', 'menghapus pendaftar: '.$registration->name);
        session()->flash('status', 'Data pendaftar dihapus.');
    }

    /**
     * Tandai pendaftar baru sebagai "Dihubungi" saat admin membuka chat WhatsApp.
     */
    public function markContacted(int $id): void
    {
        $registration = Registration::find($id);

        if (! $registration || $registration->status !== 'baru') {
            return;
        }

        $registration->update(['status' => 'dihubungi']);
        AuditLog::record('UPDATE', 'PENDAFTARAN', 'menghubungi pendaftar '.$registration->name.' via WhatsApp');
        session()->flash('status', 'Pendaftar ditandai sebagai Dihubungi.');
    }

    public function export()
    {
        $registrations = Registration::latest()->get();

        return response()->streamDownload(function () use ($registrations) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Waktu daftar', 'Nama', 'Prodi / Angkatan', 'WhatsApp', 'Motivasi', 'Status']);

            foreach ($registrations as $registration) {
                fputcsv($out, [
                    $registration->created_at?->format('Y-m-d H:i:s'),
                    $this->csvText($registration->name),
                    $this->csvText($registration->study_program),
                    $this->csvText($registration->whatsapp),
                    $this->csvText($registration->motivation),
                    $this->csvText($registration->statusLabel()),
                ]);
            }

            fclose($out);
        }, 'pendaftar-hipmi-'.now()->format('Ymd_His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function csvText($value): string
    {
        $text = (string) ($value ?? '');

        return preg_match('/^[=+\-@\t\r]/', $text) ? "'".$text : $text;
    }

    public function render()
    {
        $registrations = Registration::query()
            ->when($this->search !== '', fn ($query) => $query->where(function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('study_program', 'like', '%'.$this->search.'%');
            }))
            ->when($this->statusFilter !== 'semua', fn ($query) => $query->where('status', $this->statusFilter))
            ->latest()
            ->get();

        return view('livewire.admin.registrations', [
            'registrations' => $registrations,
            'statuses'      => Registration::STATUSES,
        ]);
    }
}
