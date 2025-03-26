<?php

namespace App\Middleware;

use App\Pipes\{AppendSignature, CapitalizeAndPunctuate, TrimTo160Characters};
use FrittenKeeZ\Vouchers\Facades\Vouchers;
use FrittenKeeZ\Vouchers\Models\Voucher;
use Illuminate\Support\Facades\Log;
use App\Actions\RedeemCashVoucher;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Arr;
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

            $reply = $this->getReply($result, $voucher);

            // Return a response indicating success
            return response()->json([
                'message' => $reply,
            ]);
        }
        Log::info("❌ No valid voucher found, continuing to other routes.");

        return $next($message, $from, $to);
    }

    protected function extractVoucherAndMobile(string $message): array
    {
        // Assuming format: "{voucher}" or "{voucher} {mobile}"
        $parts = explode(' ', trim($message), 2);
        $voucher = $parts[0] ?? null;
        $mobile = $parts[1] ?? null;

        return [$voucher, $mobile];
    }

    /** TODO: refactor this into action */
    protected function getReply(bool $result, string $voucher_code): ?string
    {
        $failed = !$result;

        $voucher = Voucher::where('code', $voucher_code)->first();
        $reply = Arr::get($voucher?->metadata, 'dedication');
        $reply = $reply
            ? app(Pipeline::class)
                ->send($reply)
                ->through([
                    CapitalizeAndPunctuate::class,
//                    AppendSignature::class,
//                    TrimTo160Characters::class,
                ])
                ->thenReturn()
            : Quote::get();

        return $failed ? null : $reply;
    }
}
