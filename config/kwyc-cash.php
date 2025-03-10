<?php

return [
    'disbursement' => [
        'server' => [
            'url' => env('DISBURSEMENT_SERVER_URL'),
            'token' => env('DISBURSEMENT_SERVER_TOKEN')
        ],
        'bank' => [
            'code' => env('DISBURSEMENT_BANK_CODE', 'GXCHPHM2XXX'),
            'via' => env('DISBURSEMENT_BANK_VIA', 'INSTAPAY'),
        ],
        'disabled' => env('DISBURSEMENT_DISABLED', true)
    ],
    'payment' => [
        'server' => [
            'url' => env('PAYMENT_SERVER_URL', 'https://fibi.disburse.cash/api/generate-qr'),
            'token' => env('PAYMENT_SERVER_TOKEN')
        ],
        'qr-code' => [
            'amount' => env('PAYMENT_AMOUNT', 50),
            'increment' => env('PAYMENT_INCREMENT', 50)
        ],
    ],
    'voucher' => [
        'value' => env('VOUCHER_VALUE', 50),
        'minimum' => env('VOUCHER_MINIMUM', 50),
        'increment' => env('VOUCHER_INCREMENT', 50),
        'tariff' => env('VOUCHER_TARIFF', 50)
    ],
    'redeem' => [
        'reference' => [
            'label' => env('REDEEM_REFERENCE_LABEL', 'Reference'),
            'value' => env('REDEEM_REFERENCE_VALUE', '')
        ],
        'success' => [
            'redirect_timeout' => env('RIDER_REDIRECT_TIMEOUT', 5000)
        ],
    ],
    'campaign' => [
        'inputs' => env('CAMPAIGN_INPUTS', '{"message": null, "location": null, "signature": null}'),
        'available-inputs' => env('CAMPAIGN_AVAILABLE_INPUTS',  'message, name, first_name, last_name, reference, location, code, signature'),
        'rider' => env('CAMPAIGN_RIDER', 'https://run.mocky.io/v3/45aab9ca-55d8-4964-bf53-d7b6f29a12c0')
    ]
];
