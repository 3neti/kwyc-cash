<?php

namespace App\Actions;

use Illuminate\Support\{Arr, Carbon, Number, Facades\Log};
use Lorisleiva\Actions\Concerns\AsAction;
use FrittenKeeZ\Vouchers\Models\Voucher;
use App\Services\OmniChannelService;
use App\Data\VoucherData;

class SendVoucherFeedback
{
    use AsAction;

    /**
     * Handle sending feedback message to the associated mobile number in the voucher metadata.
     *
     * This action extracts the feedback mobile number from the voucher metadata,
     * builds a human-friendly summary of the voucher details (e.g., code, amount, redeemed_at),
     * and sends it via the OmniChannelService SMS gateway.
     *
     * @param Voucher $voucher The voucher instance being processed.
     * @return void
     */
    public function handle(Voucher $voucher): void
    {
        $mobile = Arr::get($voucher->metadata, 'feedback');

        if (!$mobile) {
            Log::info('[SendVoucherFeedback] No feedback mobile number found.', [
                'voucher_id' => $voucher->id,
            ]);
            return;
        }

        // Format the feedback message
        $data = VoucherData::fromModel($voucher);
        $tag = $data->cash->tag ? "#" . $data->cash->tag . "\n" : "";

        $message = __(":tag:code (:amount) => :mobile\n:dt", [
            'tag'    => $tag,
            'code'   => $data->code,
            'amount' => Number::currency($data->cash->value),
            'mobile' => $data->mobile,
            'dt'     => Carbon::parse($data->redeemed_at)
                ->format('l \a\t g:i A \o\n F d'),
        ]);

        Log::info('[SendVoucherFeedback] Sending feedback message.', [
            'to'      => $mobile,
            'voucher' => $voucher->code,
            'message' => $message,
        ]);

        // Dispatch message to the feedback number
        $sms = app(OmniChannelService::class);
        $sms->send($mobile, $message);

        Log::info('[SendVoucherFeedback] Feedback message sent successfully.', [
            'to' => $mobile,
        ]);
    }
}
