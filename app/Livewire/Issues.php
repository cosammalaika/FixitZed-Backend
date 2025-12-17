<?php

namespace App\Livewire;

use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class Issues extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $statusFilter = 'all';
    public string $typeFilter = 'all';
    public string $search = '';
    public int $perPage = 20;

    public function render()
    {
        $this->perPage = max(5, min((int) setting('admin.per_page', $this->perPage), 200));
        $reports = Report::with(['reporter', 'target'])
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->typeFilter !== 'all', fn ($q) => $q->where('type', $this->typeFilter))
            ->when(strlen($this->search) > 0, fn ($q) => $q->where(function ($inner) {
                $inner->where('subject', 'like', '%' . $this->search . '%')
                    ->orWhere('message', 'like', '%' . $this->search . '%');
            }))
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.issues', [
            'reports' => $reports,
        ]);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function markStatus(int $reportId, string $status): void
    {
        $report = Report::find($reportId);
        if (! $report) {
            return;
        }

        $report->status = $status;
        if ($status === 'closed') {
            $report->resolved_at = now();
        }

        $report->save();
        session()->flash('toast', 'Issue updated.');
    }

    public function takeAction(int $reportId, string $action, int $days = 7): void
    {
        $report = Report::find($reportId);
        if (! $report) {
            return;
        }

        $defaultDays = (int) Setting::get('admin.reports.default_action_duration_days', 7);
        $useDays = $days ?: $defaultDays;
        $report->action = $action;
        $this->applyActionToUser($report->target_user_id, $action, $useDays);

        if ($action !== 'none' && $report->status === 'open') {
            $report->status = 'action_taken';
        }
        if (in_array($action, ['suspend', 'ban'], true)) {
            $report->resolved_at = now();
        }

        $report->save();
        session()->flash('toast', 'Action applied.');
    }

    protected function applyActionToUser(?int $userId, string $action, ?int $days = null): void
    {
        if (! $userId) {
            return;
        }

        $user = User::find($userId);
        if (! $user) {
            return;
        }

        if ($action === 'ban') {
            if (Schema::hasColumn('users', 'banned_at')) {
                $user->banned_at = now();
                $user->save();
            } elseif (Schema::hasColumn('users', 'status')) {
                $user->status = 'banned';
                $user->save();
            }
        } elseif ($action === 'suspend') {
            $defaultDays = (int) setting('admin.reports.default_action_duration_days', 7);
            $until = now()->addDays(max(1, (int) ($days ?? $defaultDays)));
            if (Schema::hasColumn('users', 'suspended_until')) {
                $user->suspended_until = $until;
                $user->save();
            } elseif (Schema::hasColumn('users', 'status')) {
                $user->status = 'suspended';
                $user->save();
            }
        }
    }
}
