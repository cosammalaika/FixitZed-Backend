<?php

return [
    'cash' => [
        'title' => env('PAYMENT_CASH_TITLE', 'Pay via Cash'),
        'phone' => env('PAYMENT_CASH_PHONE', '+260979871199'),
        'account' => env('PAYMENT_CASH_ACCOUNT', 'Fixitzed Subs'),
        'instructions' => env(
            'PAYMENT_CASH_INSTRUCTIONS',
            "1. Send :amount to :phone (:account).\n2. Use reference :reference in the payment description.\n3. Notify support once payment is sent."
        ),
    ],
    // 'mobile_money' => [
    //     'title' => env('PAYMENT_MOMO_TITLE', 'Pay via Mobile Money'),
    //     'phone' => env('PAYMENT_MOMO_PHONE', '+260770000000'),
    //     'account' => env('PAYMENT_MOMO_ACCOUNT', 'Fixitzed Subs'),
    //     'instructions' => env(
    //         'PAYMENT_MOMO_INSTRUCTIONS',
    //         "1. Dial your mobile money menu and send :amount to :phone (:account).\n2. Include :reference as the narration.\n3. We will credit your coins after verification."
    //     ),
    // ],
];
