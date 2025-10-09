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
            ->get()
            ->map(function (PaymentMethod $method) {
                $code = strtolower((string) $method->code);
                $config = config("payments.$code", []);

                $data = $method->toArray();
                $data['title'] = $config['title'] ?? $method->name;
                $data['instructions'] = $config['instructions'] ?? $method->integration_note;
                $data['is_manual'] = ! empty($config);
                $data['phone'] = $config['phone'] ?? null;
                $data['account'] = $config['account'] ?? null;

                return $data;
            })
            ->values();

        $existingCodes = $methods
            ->pluck('code')
            ->map(fn ($code) => strtolower((string) $code))
            ->all();

        $configMethods = collect(config('payments', []))
            ->map(function (array $config, string $code) {
                $normalized = strtolower($code);
                $fallbackName = ucfirst(str_replace('_', ' ', $normalized));

                return [
                    'id' => null,
                    'name' => $fallbackName,
                    'code' => $normalized,
                    'active' => true,
                    'sort_order' => 999,
                    'requires_integration' => false,
                    'integration_note' => $config['instructions'] ?? null,
                    'title' => $config['title'] ?? $fallbackName,
                    'instructions' => $config['instructions'] ?? null,
                    'is_manual' => true,
                    'phone' => $config['phone'] ?? null,
                    'account' => $config['account'] ?? null,
                ];
            })
            ->reject(function ($_, $code) use ($existingCodes) {
                return in_array(strtolower($code), $existingCodes, true);
            })
            ->values();

        $response = $methods->merge($configMethods)->values();

        return response()->json(['success' => true, 'data' => $response]);
    }
}
