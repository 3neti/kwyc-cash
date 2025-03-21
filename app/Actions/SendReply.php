<?php

namespace App\Actions;

use Illuminate\Support\Facades\{Log, Notification};
use Lorisleiva\Actions\Concerns\AsAction;
use FrittenKeeZ\Vouchers\Models\Voucher;
use App\Notifications\SendInspiration;

class SendReply
{
    use AsAction;

    public function handle(Voucher $voucher): void
    {
        Log::info('[SendReply] Handling reply for voucher.', ['voucher_id' => $voucher->id]);

        $mobile = $voucher->redeemers->first()?->redeemer->mobile ?? null;

        if (!$mobile) {
            Log::warning('[SendReply] No mobile number found for voucher redeemer.', [
                'voucher_id' => $voucher->id,
            ]);
            return;
        }

        Log::info('[SendReply] Sending inspirational message to mobile.', [
            'voucher_code' => $voucher->code,
            'mobile' => $mobile,
        ]);

        Notification::route('engage_spark', $mobile)
            ->notify(new SendInspiration());

        Log::info('[SendReply] Inspiration sent successfully.', [
            'mobile' => $mobile,
        ]);
    }
}
