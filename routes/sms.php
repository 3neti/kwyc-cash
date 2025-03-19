<?php

use App\Middleware\AutoReplySMS;
use App\Middleware\BlockBlacklistedWords;
use App\Middleware\CleanSMS;
use App\Middleware\LogSMS;
use App\Middleware\RateLimitSMS;
use App\Middleware\StoreSMS;
use Illuminate\Support\Facades\Log;
use App\Services\SMSRouterService;
use App\Actions\RedeemCashVoucher;
use App\Handlers\SMSLog;
use App\Handlers\SMSRedeem;

Log::info('📌 SMS Routes Loaded');

/** @var SMSRouterService $router */
$router = resolve(SMSRouterService::class);

$router->register('CASH {voucher} {mobile?}', SMSRedeem::class);
$router->register('TEST {message}', SMSLog::class);
$router->register(
    '{keyword}',
    function ($values, $from, $to) {
        return response()->json([
            'message' => "Received keyword: " . strtoupper($values['keyword']),
        ]);
    },
    [
        RateLimitSMS::class,     // Prevent spam
        CleanSMS::class,  // Normalize message
        AutoReplySMS::class,     // Auto-reply for predefined messages
        LogSMS::class,   // Log SMS
        StoreSMS::class,  // Save SMS to DB
    ]
);
