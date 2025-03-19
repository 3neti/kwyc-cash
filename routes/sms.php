<?php

use App\Middleware\{AutoReplySMS, CleanSMS, LogSMS, RateLimitSMS, StoreSMS, RedeemVoucherMiddleware};
use Illuminate\Support\Facades\Log;
use App\Services\SMSRouterService;

Log::info('📌 SMS Routes Loaded');

/** @var SMSRouterService $router */
$router = resolve(SMSRouterService::class);
Log::info("✅  Resolved SMSRouterService instance.", ['instance' => get_class($router)]);

//TODO: add transfer route

$router->register(
    '{message}',
    function ($values, $from, $to) {
        return response()->json([
            'message' => "Received message: " . strtoupper($values['message']),
        ]);
    },
    [
        RateLimitSMS::class,     // Prevent spam
        CleanSMS::class,  // Normalize message
        RedeemVoucherMiddleware::class,  // 🔥 Auto-redeem vouchers if detected
        AutoReplySMS::class,     // Auto-reply for predefined messages
        LogSMS::class,   // Log SMS
        StoreSMS::class,  // Save SMS to DB
    ]
);
