<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    /**
     * GET /api/payment-methods
     * Returns active methods ordered for the client to render.
     */
    public function index()
    {
        $methods = PaymentMethod::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'code', 'active', 'sort_order', 'requires_integration', 'integration_note']);

        return response()->json(['success' => true, 'data' => $methods]);
    }
}
