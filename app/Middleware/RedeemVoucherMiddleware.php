<?php

namespace App\Middleware;

use FrittenKeeZ\Vouchers\Facades\Vouchers;
use Illuminate\Support\Facades\Log;
use App\Actions\RedeemCashVoucher;
use App\Facades\Quote;
use Closure;

class RedeemVoucherMiddleware implements SMSMiddlewareInterface
{
    public function handle(string $message, string $from, string $to, Closure $next)
    {
        Log::info("🔎 Checking if SMS is a redeemable voucher", compact('message', 'from', 'to'));

        // Extract voucher and mobile (optional)
        [$voucher, $mobile] = $this->extractVoucherAndMobile($message);

        // Check if voucher exists and is available for redemption
        if (Vouchers::redeemable($voucher)) {
            // If mobile is null, assign the sender's number (`$from`)
            $mobile = $mobile ?: $from;

            Log::info("✅ Voucher detected and redeemable", compact('voucher', 'mobile'));

            // Handle redemption (execute `RedeemCashVoucher` handler)
            $result = app(RedeemCashVoucher::class)->run($voucher, $mobile);
            Log::info("🛠 Running RedeemVoucherMiddleware Middleware", compact('message', 'from', 'to'));


            return Quote::get();
//
//            // Return a response indicating success
//            return response()->json([
//                'message' => "Voucher successfully redeemed!",
//                'voucher' => $voucher,
//                'mobile' => $mobile,
//                'result' => $result,
//            ]);
        }
        Log::info("❌ No valid voucher found, continuing to other routes.");

        return $next($message, $from, $to);
    }

    private function extractVoucherAndMobile(string $message): array
    {
        // Assuming format: "{voucher}" or "{voucher} {mobile}"
        $parts = explode(' ', trim($message), 2);
        $voucher = $parts[0] ?? null;
        $mobile = $parts[1] ?? null;

        return [$voucher, $mobile];
    }
}
