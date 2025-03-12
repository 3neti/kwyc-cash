<?php

namespace App\Console\Commands;

use App\Actions\RetryDisbursement as RetryDisbursementAction;
use FrittenKeeZ\Vouchers\Models\Voucher;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;

/**
 * RetryDisburseCashVoucher is a console command that attempts to re-disburse
 * a cash voucher using a provided voucher code.
 *
 * This command is useful for retrying failed disbursements or manually triggering
 * disbursement processes.
 */
class RetryDisburseCashVoucher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:retry-disburse {voucher_code : The voucher code to re-disburse}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-disburse a cash voucher using the given voucher code.';

    /**
     * Execute the console command.
     *
     * This method retrieves the voucher by code and attempts to re-disburse the associated cash.
     * If successful, it logs and displays a success message; otherwise, it logs and outputs an error.
     */
    public function handle()
    {
        $voucherCode = $this->argument('voucher_code');
        Log::info("Retrying cash disbursement for voucher: {$voucherCode}");

        // Retrieve the voucher
        $voucher = Voucher::where('code', $voucherCode)->first();

        // Check if the voucher exists
        if (!$voucher instanceof Voucher) {
            $this->error("Voucher with code '{$voucherCode}' not found.");
            Log::error("Failed to re-disburse: Voucher '{$voucherCode}' not found.");
            return Command::FAILURE;
        }

        // Attempt to re-disburse the cash
        $this->info("Attempting to re-disburse voucher code: {$voucherCode}");
        $success = RetryDisbursementAction::run($voucher);

        if ($success) {
            $this->info('Cash disbursed successfully!');
            Log::info("Successfully re-disbursed cash for voucher: {$voucherCode}");
            return Command::SUCCESS;
        } else {
            $this->error('Failed to re-disburse voucher.');
            Log::error("Failed to re-disburse cash for voucher: {$voucherCode}");
            return Command::FAILURE;
        }
    }
}
