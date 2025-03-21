<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use FrittenKeeZ\Vouchers\Models\Voucher;
use Illuminate\Support\Facades\Log;

class ProcessVoucherRedemption
{
    use AsAction;

    /**
     * Handle the voucher redemption process.
     *
     * @param Voucher $voucher
     * @return void
     * @throws \Exception
     */
    public function handle(Voucher $voucher): void
    {
        try {
            Log::info("Processing voucher redemption for Voucher Code: {$voucher->code}");

            // Disburse the amount and notify feedback in a sequence
//            DisburseAmount::run($voucher) && SendFeedback::run($voucher) && SendReply::run($voucher);

            if (DisburseAmount::run($voucher)) {
                SendFeedback::run($voucher);
                SendReply::run($voucher);
            }

            Log::info("Successfully processed voucher redemption for Voucher Code: {$voucher->code}");

        } catch (\Exception $e) {
            Log::error('Failed to process voucher redemption', [
                'voucher_code' => $voucher->code ?? 'N/A',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
