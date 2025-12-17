<?php

namespace App\Livewire\Audit;

use App\Models\LoginAudit;
use Livewire\Component;
use Livewire\WithPagination;

class LoginAuditIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = 'all';
    public string $event = 'all';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'all'],
        'event' => ['except' => 'all'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingEvent(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $perPage = (int) setting('admin.per_page', 20);
        $perPage = max(5, min($perPage, 200));
        $audits = LoginAudit::with('user')
            ->when($this->status !== 'all', fn ($query) => $query->where('status', $this->status))
            ->when($this->event !== 'all', fn ($query) => $query->where('event', $this->event))
            ->when($this->search !== '', function ($query) {
                $query->where(function ($inner) {
                    $inner->where('ip_address', 'like', '%' . $this->search . '%')
                        ->orWhere('user_agent', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', function ($sub) {
                            $sub->where('email', 'like', '%' . $this->search . '%')
                                ->orWhere('username', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->latest()
            ->paginate($perPage);

        $events = LoginAudit::query()
            ->select('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event')
            ->all();

        return view('livewire.audit.login-audit-index', [
            'audits' => $audits,
            'events' => $events,
        ]);
    }
}
