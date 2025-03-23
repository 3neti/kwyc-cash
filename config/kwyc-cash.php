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
//            'url' => env('PAYMENT_SERVER_URL', 'https://fibi.disburse.cash/api/generate-qr'),
            'url' => env('PAYMENT_SERVER_URL', 'https://fibi.seqrcode.net/api/generate-qr'),
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
        'tariff' => env('VOUCHER_TARIFF', 50),
        'duration' => env('VOUCHER_DURATION', 'PT12H')
    ],
    'redeem' => [
        'reference' => [
            'label' => env('REDEEM_REFERENCE_LABEL', 'Reference'),
            'value' => env('REDEEM_REFERENCE_VALUE', '')
        ],
        'success' => [
            'redirect_timeout' => env('RIDER_REDIRECT_TIMEOUT', 5000)
        ],
        'auto_feedback' => (bool) env('REDEEM_AUTO_FEEDBACK', true)
    ],
    'campaign' => [
        'name' => env('CAMPAIGN_NAME', 'Default'),
        'inputs' => env('CAMPAIGN_INPUTS', '{"message": null, "location": null, "signature": null, "reference": "AA537"}'),
        'available-inputs' => env('CAMPAIGN_AVAILABLE_INPUTS',  'message, name, first_name, last_name, reference, location, code, signature'),
        'rider' => env('CAMPAIGN_RIDER', 'https://run.mocky.io/v3/45aab9ca-55d8-4964-bf53-d7b6f29a12c0'),
        'dedication' => env('CAMPAIGN_DEDICATION')
    ],
    'currency' => env('CURRENCY', 'PHP'),
    'system' => [
        'user' => [
            'name' => env('SYSTEM_NAME', 'System User'),
            'email' => env('SYSTEM_EMAIL', 'lester@hurtado.ph'),
            'mobile' => env('SYSTEM_MOBILE', '09173011987'),
            'password' => env('SYSTEM_PASSWORD', 'password'),
            'country' => env('SYSTEM_COUNTRY', 'PH'),
            'prefund' => env('SYSTEM_PREFUND', 1000000000.0)
        ]
    ],
    'ui' => [
        'vouchers' => [
            'pages' => env('VOUCHERS_PAGES', 10)
        ]
    ],
    'sms' => [
        'allowed' => [
            'transfer' => array_filter(explode(',', env('SMS_ALLOWED_TRANSFER', ''))),
            'generate' => array_filter(explode(',', env('SMS_ALLOWED_GENERATE', ''))),
        ],
    ],
    'omni-channel' => [
        'url' => env('OMNI_CHANNEL_URL', 'http://13.228.103.95:8063/core/sender'),
        'access_key' => env('OMNI_CHANNEL_ACCESS_KEY'),
        'service' => env('OMNI_CHANNEL_SERVICE', 'mt'),
    ],
];
