<?php

namespace App\Providers;

use FrittenKeeZ\Vouchers\Models\Voucher;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Vite;
use App\Actions\DisburseAmount;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Voucher::redeemed(function (Voucher $voucher) {
            DisburseAmount::dispatch($voucher);
        });
    }
}
