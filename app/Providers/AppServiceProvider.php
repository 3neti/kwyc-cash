<?php

namespace App\Providers;

use App\Services\{OmniChannelService, QuoteService, SMSRouterService};
use Illuminate\Support\Facades\{File, Vite};
use App\Actions\ProcessVoucherRedemption;
use FrittenKeeZ\Vouchers\Models\Voucher;
use Illuminate\Support\ServiceProvider;
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

        $this->app->singleton(QuoteService::class, function () {
            return new QuoteService();
        });

        $this->app->singleton(OmniChannelService::class, function () {
            return new OmniChannelService(
                config('kwyc-cash.omni-channel.url'),
                config('kwyc-cash.omni-channel.access_key'),
                config('kwyc-cash.omni-channel.service')
            );
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
