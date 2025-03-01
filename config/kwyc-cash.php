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
    ],
];
