<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use FrittenKeeZ\Vouchers\Models\Voucher;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use App\Models\Cash;

/**
 * RetryDisbursement is responsible for retrying the disbursement of cash
 * associated with a voucher.
 *
 * This action checks whether the cash associated with a given voucher
 * has already been disbursed. If not, it attempts to disburse the amount again.
 */
class RetryDisbursement
{
    use AsAction;

    /**
     * Handles the retry logic for disbursing a voucher's associated cash.
     *
     * @param Voucher $voucher The voucher associated with the cash disbursement.
     * @return bool Returns true if disbursement was successful, false otherwise.
     */
    public function handle(Voucher $voucher): bool
    {
        Log::info("Initiating retry disbursement for voucher: {$voucher->code}");

        // Retrieve the first associated cash entity from the voucher
        $cash = $voucher->getEntities(Cash::class)->first();

        // Ensure cash entity exists and has not yet been disbursed
        if ($cash instanceof Cash) {
            Log::info("Cash entity found for voucher: {$voucher->code}", [
                'disbursed' => $cash->disbursed,
            ]);

            if (!$cash->disbursed) {
                // Extract metadata to retrieve mobile number and country
                $metadata = Arr::only($voucher->redeemers->first()->metadata, ['mobile', 'country']);
                $mobile = $metadata['mobile'] ?? null;
                $country = $metadata['country'] ?? null;

                Log::info("Attempting to disburse cash for voucher: {$voucher->code}", [
                    'mobile' => $mobile,
                    'country' => $country,
                ]);

                // Execute the DisburseAmount action
                $result = DisburseAmount::run($voucher, $mobile, $country);

                if ($result) {
                    Log::info("Cash successfully disbursed for voucher: {$voucher->code}");
                } else {
                    Log::warning("Failed to disburse cash for voucher: {$voucher->code}");
                }

                return $result;
            } else {
                Log::info("Cash already disbursed for voucher: {$voucher->code}");
            }
        } else {
            Log::warning("No cash entity found for voucher: {$voucher->code}");
        }

        return false;
    }
}
