<?php

use Illuminate\Support\Facades\Log;
use App\Services\SMSRouterService;
use App\Actions\RedeemCashVoucher;

Log::info('📌 SMS Routes Loaded');

/** @var SMSRouterService $router */
$router = resolve(SMSRouterService::class);

$router->register('LOG {message}', function (array $values, string $from, string $to) {
    Log::info("Logging SMS Message", [
        'message' => $values['message'],
        'from' => $from,
        'to' => $to,
    ]);

    return response()->json([
        'message' => "Logged: " . $values['message'],
        'from' => $from,
        'to' => $to,
    ]);
});

$router->register('CASH {voucher}', function (array $values, string $from, string $to) {
    Log::info("Redeeming SMS Message", [
        'voucher' => $values['voucher'],
        'from' => $from,
        'to' => $to,
    ]);

    $action = app(RedeemCashVoucher::class);
    $action->run($values['voucher'], $from);

    return response()->json([
        'voucher' => "Redeemed: " . $values['voucher'],
        'from' => $from,
        'to' => $to,
    ]);
});

