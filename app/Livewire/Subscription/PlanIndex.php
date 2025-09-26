<?php

namespace App\Livewire\Subscription;

use App\Models\SubscriptionPlan;
use Livewire\Component;
use Illuminate\Support\Facades\Schema;

class PlanIndex extends Component
{
    public function render()
    {
        $missing = ! Schema::hasTable('subscription_plans');
        $plans = $missing ? collect() : SubscriptionPlan::orderBy('price_cents')->get();
        return view('livewire.subscription.plan-index', compact('plans', 'missing'));
    }
}
