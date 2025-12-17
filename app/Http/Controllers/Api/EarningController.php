<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EarningController extends Controller
{
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $fixer = $user?->fixer;

        if (! $fixer) {
            throw ValidationException::withMessages([
                'fixer' => ['Only fixers can view earnings history.'],
            ]);
        }

        $range = (string) $request->query('filter', 'all');
        $presets = $this->earningPresets();

        $query = Payment::with(['serviceRequest.service'])
            ->whereHas('serviceRequest', function ($q) use ($fixer) {
                $q->where('fixer_id', $fixer->id);
            })
            ->whereNotNull('paid_at')
            ->orderByDesc('paid_at');

        $now = now();
        if (preg_match('/^(\d+)d$/', $range, $matches)) {
            $days = (int) $matches[1];
            if (in_array($days, $presets, true)) {
                $query->where('paid_at', '>=', $now->copy()->subDays($days));
            }
        } else {
            switch ($range) {
                case 'year':
                    $query->where('paid_at', '>=', $now->copy()->startOfYear());
                    break;
                case 'all':
                default:
                    // no date filter
                    break;
            }
        }

        $payments = $query->get()->map(function (Payment $payment) {
            $service = $payment->serviceRequest;
            return [
                'id' => $payment->id,
                'amount' => (float) $payment->amount,
                'payment_method' => $payment->payment_method,
                'transaction_id' => $payment->transaction_id,
                'service_request_id' => $service?->id,
                'service_name' => $service?->service?->name,
                'scheduled_at' => $service?->scheduled_at?->toIso8601String(),
                'paid_at' => $payment->paid_at?->toIso8601String() ?? $payment->created_at?->toIso8601String(),
                'location' => $service?->location,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    private function earningPresets(): array
    {
        $raw = (string) setting('earnings.filter_presets_days', '7,30,90');
        $parts = array_filter(array_map('trim', explode(',', $raw)));
        $values = [];

        foreach ($parts as $part) {
            if (! ctype_digit($part)) {
                return [7, 30, 90];
            }
            $value = (int) $part;
            if ($value < 1 || $value > 365) {
                return [7, 30, 90];
            }
            $values[] = $value;
        }

        if (empty($values) || count($values) > 10) {
            return [7, 30, 90];
        }

        return array_values(array_unique($values));
    }
}
