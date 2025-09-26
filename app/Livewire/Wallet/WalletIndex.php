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
        $wallets = $missing ? collect() : FixerWallet::with('fixer.user')->latest()->get();
        return view('livewire.wallet.wallet-index', compact('wallets', 'missing'));
    }
}

