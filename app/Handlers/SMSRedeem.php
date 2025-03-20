<?php

namespace App\Handlers;

use App\Contracts\SMSHandlerInterface;
use App\Actions\RedeemCashVoucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class SMSRedeem implements SMSHandlerInterface
{
    /**
     * @deprecated
     * Handle SMS message logging as an invokable class.
     */
    public function __invoke(array $values, string $from, string $to): JsonResponse
    {
        $action = app(RedeemCashVoucher::class);
        $mobile = Arr::get($values, 'mobile', $from);

        $redeemed = $action->run(voucher_code: $values['voucher'], mobile: $mobile);

        return response()->json([
            'message' => ($redeemed ? "Redeemed: " : "Not Redeemed: ") .  $values['voucher'],
            'from' => $from,
            'to' => $to,
        ]);
    }
}
