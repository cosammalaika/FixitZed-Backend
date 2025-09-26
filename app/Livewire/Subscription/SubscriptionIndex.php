<?php

namespace App\Livewire\Subscription;

use App\Models\FixerSubscription;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Schema;

class SubscriptionIndex extends Component
{
    use WithPagination;

    public function render()
    {
        $missing = ! Schema::hasTable('fixer_subscriptions');
        $purchases = $missing ? collect() : FixerSubscription::with(['fixer.user', 'plan'])->latest()->paginate(20);
        return view('livewire.subscription.subscription-index', compact('purchases', 'missing'));
    }
}
