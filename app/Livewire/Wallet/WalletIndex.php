<?php

namespace App\Livewire\Wallet;

use App\Models\FixerWallet;
use Livewire\Component;
use Illuminate\Support\Facades\Schema;

class WalletIndex extends Component
{
    public function render()
    {
        $missing = ! Schema::hasTable('fixer_wallets');
        $wallets = $missing ? collect() : FixerWallet::query()
            ->with(['fixer.user'])
            ->whereNotNull('fixer_id')
            ->latest()
            ->get();

        if (! $missing) {
            $broken = FixerWallet::query()
                ->whereDoesntHave('fixer')
                ->orWhereHas('fixer', fn ($q) => $q->whereDoesntHave('user'))
                ->count();

            logger()->warning('Wallets page: broken relations detected', [
                'broken_count' => $broken,
            ]);
        }
        return view('livewire.wallet.wallet-index', compact('wallets', 'missing'));
    }
}
