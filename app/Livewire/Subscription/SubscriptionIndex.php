<?php

namespace App\Livewire\Subscription;

use App\Models\FixerSubscription;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Schema;
use App\Services\WalletService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SubscriptionIndex extends Component
{
    use WithPagination;

    public $selectedId;
    public $selectedReference;
    public $selectedPlan;
    public $selectedFixer;
    public $selectedAmount;
    public $selectedMethod;
    public $selectedInstructions;
    public $showingId;
    public $showData = [];
    public $showInstructions = [];
    public $editData = [
        'id' => null,
        'payment_reference' => '',
        'amount' => '',
        'status' => 'pending',
        'coins_awarded' => '',
        'starts_at' => '',
        'expires_at' => '',
    ];
    public $deleteId;
    public $deleteSummary;
    public $processing = false;
    public $errorMessage;
    public $showApproveModal = false;
    public $showViewModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;

    protected $rules = [
        'editData.payment_reference' => 'nullable|string|max:191',
        'editData.amount' => 'nullable|numeric|min:0',
        'editData.status' => 'required|in:pending,approved,failed,cancelled,expired',
        'editData.coins_awarded' => 'nullable|integer|min:0',
        'editData.starts_at' => 'nullable|date',
        'editData.expires_at' => 'nullable|date|after_or_equal:editData.starts_at',
    ];

    public function render()
    {
        $perPage = (int) setting('admin.per_page', 20);
        $perPage = max(5, min($perPage, 200));
        $missing = ! Schema::hasTable('fixer_subscriptions');
        $purchases = $missing ? collect() : FixerSubscription::with(['fixer.user', 'plan'])
            ->latest()
            ->paginate($perPage);
        return view('livewire.subscription.subscription-index', compact('purchases', 'missing'))
            ->with('statuses', $this->statuses);
    }

    public function confirmApproval(int $id): void
    {
        if (! $this->authorizeAction()) return;

        $this->resetValidation();
        $this->resetSelection();

        $subscription = FixerSubscription::with(['plan', 'fixer.user'])->findOrFail($id);
        if ($subscription->status === 'approved') {
            $this->errorMessage = 'Subscription is already approved.';
            return;
        }

        $this->selectedId = $subscription->id;
        $this->selectedReference = $subscription->payment_reference;
        $this->selectedPlan = optional($subscription->plan)->name;
        $fixerUser = optional($subscription->fixer)->user;
        $this->selectedFixer = $fixerUser ? trim(($fixerUser->first_name ?? '') . ' ' . ($fixerUser->last_name ?? '')) ?: $fixerUser->name : null;
        $this->selectedAmount = $subscription->amount_paid_cents !== null
            ? round(((int) $subscription->amount_paid_cents) / 100, 2)
            : null;
        $this->selectedMethod = $subscription->payment_method;
        $this->selectedInstructions = $subscription->payment_instructions;
        $this->errorMessage = null;
        $this->processing = false;
        $this->showApproveModal = true;
    }

    public function approveSelected(WalletService $walletService): void
    {
        if (! $this->authorizeAction()) return;

        if (! $this->selectedId) {
            $this->errorMessage = 'Select a subscription to approve.';
            return;
        }

        $this->processing = true;
        $this->errorMessage = null;

        try {
            $subscription = FixerSubscription::with(['plan', 'fixer.user'])->findOrFail($this->selectedId);
            $walletService->approveSubscriptionAndCredit($subscription);
            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'success',
                'message' => 'Subscription approved and coins credited.',
            ]);
            $this->resetSelection();
            $this->dispatch('subscriptionApproved');
            $this->showApproveModal = false;
        } catch (ValidationException $e) {
            $this->errorMessage = $e->getMessage();
            Log::warning('Manual subscription approval failed', [
                'subscription_id' => $this->selectedId,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ]);
        } catch (\Throwable $e) {
            $this->errorMessage = 'Unable to approve subscription. Please try again.';
            Log::error('Manual subscription approval error', [
                'subscription_id' => $this->selectedId,
                'message' => $e->getMessage(),
            ]);
        } finally {
            $this->processing = false;
        }
    }

    public function cancelApproval(): void
    {
        $this->resetSelection();
        $this->showApproveModal = false;
    }

    public function showSubscription(int $id): void
    {
        if (! $this->authorizeAction()) return;
        $this->resetValidation();

        $subscription = FixerSubscription::with(['plan', 'fixer.user'])->findOrFail($id);

        $this->showingId = $subscription->id;
        $this->showData = [
            'Fixer' => optional(optional($subscription->fixer)->user)->name
                ?? trim((optional(optional($subscription->fixer)->user)->first_name ?? '') . ' ' . (optional(optional($subscription->fixer)->user)->last_name ?? ''))
                ?: '—',
            'Plan' => optional($subscription->plan)->name ?? '—',
            'Coins' => (int) ($subscription->coins_awarded ?? 0),
            'Status' => ucfirst((string) $subscription->status),
            'Amount' => $subscription->amount_paid_cents !== null
                ? 'K' . number_format(((int) $subscription->amount_paid_cents) / 100, 2, '.', ',')
                : '—',
            'Reference' => $subscription->payment_reference ?? '—',
            'Payment Method' => $subscription->payment_method ? ucfirst(str_replace('_', ' ', $subscription->payment_method)) : '—',
            'Created' => optional($subscription->created_at)?->format('d M Y • H:i') ?? '—',
            'Starts' => optional($subscription->starts_at)?->format('d M Y • H:i') ?? '—',
            'Expires' => optional($subscription->expires_at)?->format('d M Y • H:i') ?? '—',
        ];

        $instructions = $subscription->payment_instructions ?? '';
        $this->showInstructions = collect(preg_split('/\r?\n/', (string) $instructions))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        $this->showViewModal = true;
    }

    public function closeView(): void
    {
        $this->showingId = null;
        $this->showData = [];
        $this->showInstructions = [];
        $this->showViewModal = false;
    }

    public function editSubscription(int $id): void
    {
        if (! $this->authorizeAction()) return;
        $this->resetValidation();

        $subscription = FixerSubscription::with(['plan', 'fixer.user'])->findOrFail($id);

        $this->editData = [
            'id' => $subscription->id,
            'payment_reference' => $subscription->payment_reference ?? '',
            'amount' => $subscription->amount_paid_cents !== null
                ? number_format(((int) $subscription->amount_paid_cents) / 100, 2, '.', '')
                : '',
            'status' => (string) ($subscription->status ?? 'pending'),
            'coins_awarded' => $subscription->coins_awarded ?? '',
            'starts_at' => optional($subscription->starts_at)?->format('Y-m-d\TH:i') ?? '',
            'expires_at' => optional($subscription->expires_at)?->format('Y-m-d\TH:i') ?? '',
        ];

        $this->showEditModal = true;
    }

    public function cancelEditing(): void
    {
        $this->editData = $this->defaultEditData();
        $this->resetValidation();
        $this->showEditModal = false;
    }

    public function updateSubscription(WalletService $walletService): void
    {
        if (! $this->authorizeAction()) return;

        $this->validate();

        $id = $this->editData['id'] ?? null;
        if (! $id) {
            $this->errorMessage = 'No subscription selected.';
            return;
        }

        $subscription = FixerSubscription::findOrFail($id);
        $originalStatus = $subscription->status;

        $subscription->payment_reference = $this->editData['payment_reference'] ?: null;
        $subscription->coins_awarded = $this->editData['coins_awarded'] !== ''
            ? (int) $this->editData['coins_awarded']
            : null;

        $amount = $this->editData['amount'];
        $subscription->amount_paid_cents = $amount !== ''
            ? (int) round(((float) $amount) * 100)
            : null;

        $subscription->status = $this->editData['status'] ?? 'pending';

        $startsAt = $this->editData['starts_at'] ? Carbon::parse($this->editData['starts_at']) : null;
        $expiresAt = $this->editData['expires_at'] ? Carbon::parse($this->editData['expires_at']) : null;
        $subscription->starts_at = $startsAt;
        $subscription->expires_at = $expiresAt;

        $subscription->save();

        if ($originalStatus !== 'approved' && $subscription->status === 'approved') {
            $walletService->approveSubscriptionAndCredit($subscription);
        }

        $this->dispatchBrowserEvent('flash-message', [
            'type' => 'success',
            'message' => 'Subscription updated successfully.',
        ]);
        $this->cancelEditing();
        $this->dispatch('subscriptionApproved');
    }

    public function confirmDelete(int $id): void
    {
        if (! $this->authorizeAction()) return;
        $subscription = FixerSubscription::with(['plan', 'fixer.user'])->findOrFail($id);
        $this->deleteId = $subscription->id;
        $plan = optional($subscription->plan)->name ?? 'Unknown plan';
        $fixer = optional(optional($subscription->fixer)->user)->name
            ?? trim((optional(optional($subscription->fixer)->user)->first_name ?? '') . ' ' . (optional(optional($subscription->fixer)->user)->last_name ?? ''))
            ?: 'a fixer';
        $this->deleteSummary = $plan . ' for ' . $fixer;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->deleteId = null;
        $this->deleteSummary = null;
        $this->showDeleteModal = false;
    }

    public function deleteSubscription(): void
    {
        $this->authorizeAction();

        if (! $this->deleteId) {
            $this->errorMessage = 'No subscription selected for deletion.';
            return;
        }

        try {
            $subscription = FixerSubscription::findOrFail($this->deleteId);
            $subscription->delete();
            $this->dispatchBrowserEvent('flash-message', [
                'type' => 'success',
                'message' => 'Subscription deleted.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to delete subscription', [
                'subscription_id' => $this->deleteId,
                'message' => $e->getMessage(),
            ]);
            $this->errorMessage = 'Unable to delete subscription. Please try again.';
        }

        $this->cancelDelete();
        $this->dispatch('subscriptionApproved');
    }

    public function getStatusesProperty(): array
    {
        return [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
            'expired' => 'Expired',
        ];
    }

    protected function resetSelection(): void
    {
        $this->reset([
            'selectedId',
            'selectedReference',
            'selectedPlan',
            'selectedFixer',
            'selectedAmount',
            'selectedMethod',
            'selectedInstructions',
            'processing',
            'errorMessage',
        ]);
        $this->showingId = null;
        $this->showData = [];
        $this->showInstructions = [];
        $this->editData = $this->defaultEditData();
        $this->deleteId = null;
        $this->deleteSummary = null;
        $this->resetValidation();
        $this->showApproveModal = false;
        $this->showViewModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
    }

    protected function authorizeAction(): bool
    {
        try {
            if (method_exists(Gate::class, 'authorize')) {
                Gate::authorize('manage-subscriptions');
                return true;
            }
        } catch (\Throwable $e) {
            $this->errorMessage = 'You do not have permission to perform this action.';
            return false;
        }

        return true;
    }

    protected function defaultEditData(): array
    {
        return [
            'id' => null,
            'payment_reference' => '',
            'amount' => '',
            'status' => 'pending',
            'coins_awarded' => '',
            'starts_at' => '',
            'expires_at' => '',
        ];
    }
}
