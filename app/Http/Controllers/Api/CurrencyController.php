<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class CurrencyController extends Controller
{
    public function show()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'code' => Setting::get('currency.code', 'ZMW'),
                'symbol' => Setting::get('currency.symbol', 'ZMW'),
                'name' => Setting::get('currency.name', 'Zambian Kwacha'),
            ],
        ]);
    }
}
