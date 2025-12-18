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

        $range = $request->query('filter', 'all');

        $query = Payment::with(['serviceRequest.service'])
            ->whereHas('serviceRequest', function ($q) use ($fixer) {
                $q->where('fixer_id', $fixer->id);
            })
            ->whereNotNull('paid_at')
            ->orderByDesc('paid_at');

        $now = now();
        switch ($range) {
            case '7d':
                $query->where('paid_at', '>=', $now->copy()->subDays(7));
                break;
            case '30d':
                $query->where('paid_at', '>=', $now->copy()->subDays(30));
                break;
            case '90d':
                $query->where('paid_at', '>=', $now->copy()->subDays(90));
                break;
            case 'year':
                $query->where('paid_at', '>=', $now->copy()->startOfYear());
                break;
            case 'all':
            default:
                // no date filter
                break;
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
}

