<?php

namespace App\Providers;

use Illuminate\Support\Facades\{File, Log, Vite};
use App\Actions\ProcessVoucherRedemption;
use FrittenKeeZ\Vouchers\Models\Voucher;
use Illuminate\Support\ServiceProvider;
use App\Services\SMSRouterService;
use Illuminate\Support\Number;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Ensure SMSRouterService is a singleton (shared instance)
        $this->app->singleton(SMSRouterService::class, function ($app) {
            return new SMSRouterService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Voucher::redeemed(function (Voucher $voucher) {
            ProcessVoucherRedemption::dispatch($voucher);
//            DisburseAmount::dispatch($voucher);
        });
        Number::useCurrency(config('kwyc-cash.currency'));

        if (File::exists(base_path('routes/sms.php'))) {
            require base_path('routes/sms.php');
        }
    }
}
