<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Actions\RedeemCashVoucher as RedeemCashVoucherAction;

class RedeemCashVoucher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:redeem
                            {voucher_code : The voucher code to redeem}
                            {mobile : The mobile number associated with the voucher}
                            {--country=PH : The country code, default is PH}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Redeems a cash voucher using the given voucher code and mobile number';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $voucherCode = $this->argument('voucher_code');
        $mobile = $this->argument('mobile');
        $country = $this->option('country') ?? 'PH';

        $this->info("Attempting to redeem voucher code: {$voucherCode} for mobile: {$mobile} in country: {$country}");

        $success = RedeemCashVoucherAction::run($voucherCode, $mobile, $country);

        if ($success) {
            $this->info('Voucher redeemed successfully!');
        } else {
            $errorMessage = RedeemCashVoucherAction::getErrorMessage() ?? 'Failed to redeem voucher.';
            $this->error($errorMessage);
        }
    }
}
