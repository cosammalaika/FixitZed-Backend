<?php

namespace App\Livewire\Wallet;

use App\Models\FixerWallet;
use App\Models\Fixer;
use App\Models\Notification;
use App\Models\WalletLedger;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class WalletAdjust extends Component
{
    public $fixerId;
    public $delta = 0;
    public $reason = 'admin_adjustment';
    public $note = '';

    protected $rules = [
        'delta' => 'required|integer|not_in:0',
        'reason' => 'required|string',
        'note' => 'nullable|string',
    ];

    public function mount(int $fixerId): void
    {
        $this->fixerId = $fixerId;
    }

    public function save(WalletService $walletService)
    {
        $this->validate();
        try {
            $deltaApplied = (int) $this->delta;
            $expiresAt = null;
            DB::transaction(function () use ($walletService, $deltaApplied, &$expiresAt) {
                $wallet = FixerWallet::where('fixer_id', $this->fixerId)->lockForUpdate()->first();
                if (! $wallet) {
                    $wallet = FixerWallet::create([
                        'fixer_id' => $this->fixerId,
                        'coin_balance' => 0,
                        'subscription_status' => 'pending',
                    ]);
                }

                $wallet->coin_balance = max(0, (int) $wallet->coin_balance + $deltaApplied);
                $wallet->subscription_status = $walletService->computeStatus($wallet->coin_balance, $wallet->last_subscription_expires_at);
                $expiresAt = $wallet->last_subscription_expires_at; // capture for message context
                $wallet->save();

                WalletLedger::create([
                    'fixer_id' => $this->fixerId,
                    'delta' => $deltaApplied,
                    'reason' => $this->reason,
                    'meta' => ['note' => $this->note],
                ]);
            });
        } catch (\Throwable $e) {
            session()->flash('error', 'Failed to adjust wallet: ' . $e->getMessage());
            return;
        }

        // Notify the Fixer user about the adjustment
        $fixer = Fixer::with('user')->find($this->fixerId);
        if ($fixer && $fixer->user) {
            $deltaText = ($deltaApplied >= 0 ? '+' : '') . $deltaApplied;
            $title = 'Wallet adjusted';
            $msg = "Your wallet was adjusted by {$deltaText} coin(s).";
            if (!empty($this->note)) {
                $msg .= " Note: {$this->note}";
            }
            Notification::create([
                'recipient_type' => 'Individual',
                'user_id' => $fixer->user->id,
                'title' => $title,
                'message' => $msg,
                'read' => false,
            ]);
        }

        session()->flash('success', 'Wallet adjusted successfully.');
        // Force refresh of the list so new balance is visible
        return redirect()->route('wallet.index');
    }

    public function render()
    {
        return view('livewire.wallet.wallet-adjust');
    }
}
