<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Earning;
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
        $query = Earning::where('fixer_id', $fixer->id)->latest();

        switch ($range) {
            case '7d':
                $query->where('created_at', '>=', now()->subDays(7));
                break;
            case '30d':
                $query->where('created_at', '>=', now()->subDays(30));
                break;
            case '90d':
                $query->where('created_at', '>=', now()->subDays(90));
                break;
            case 'year':
                $query->where('created_at', '>=', now()->subYear());
                break;
            case 'all':
            default:
                // no additional filter
                break;
        }

        $earnings = $query->get()->map(function (Earning $earning) {
            return [
                'id' => $earning->id,
                'type' => 'earning',
                'amount' => (float) $earning->amount,
                'service_count' => $earning->service_count,
                'created_at' => $earning->created_at?->toIso8601String(),
                'note' => $earning->service_count ? $earning->service_count . ' services' : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $earnings,
        ]);
    }
}

