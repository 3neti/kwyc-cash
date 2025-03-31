<?php

use App\Middleware\{AuthorizeSMS, AutoReplySMS, CleanSMS, LogSMS, RateLimitSMS, StoreSMS, RedeemVoucherMiddleware};
use App\Handlers\{SMSGenerate, SMSTransfer};
use Illuminate\Support\Facades\Log;
use App\Services\SMSRouterService;

Log::info('📌 SMS Routes Loaded');

/** @var SMSRouterService $router */
$router = resolve(SMSRouterService::class);
//Log::info("✅  Resolved SMSRouterService instance.", ['instance' => get_class($router)]);

$router->register('TRANSFER {mobile} {amount}', SMSTransfer::class);
$router->register('GENERATE {extra?}', SMSGenerate::class);//TODO: add tag

$router->register(
    '{message}',
    function ($values, $from, $to) {
        Log::info("📩 SMS Route Matched", ['message' => $values['message'], 'from' => $from, 'to' => $to]);

        return response()->json([
            'message' => null
        ]);
    },
    [
        RateLimitSMS::class,     // Prevent spam
        CleanSMS::class,  // Normalize message
        AutoReplySMS::class,     // Auto-reply for predefined messages
        RedeemVoucherMiddleware::class,  // 🔥 Auto-redeem vouchers if detected
//        LogSMS::class,   // Log SMS
//        StoreSMS::class,  // Save SMS to DB
    ]
);
