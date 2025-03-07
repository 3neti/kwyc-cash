<?php

use App\Http\Controllers\RedeemCashVoucherController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::resource('redeem', RedeemCashVoucherController::class)->parameter('redeem', 'voucher')->only(['create', 'store', 'show']);

use App\Http\Controllers\VoucherController;
use App\Http\Controllers\WalletController;

Route::middleware('auth')->group(function () {
    Route::resource('vouchers', VoucherController::class)->only('index', 'create', 'store');
    Route::resource('wallet', WalletController::class)->only('create');
    Route::resource('campaign', CampaignController::class)->only('create');
});

use App\Http\Controllers\Auth\MobileAuthController;

Route::post('/auth/login-by-mobile', [MobileAuthController::class, 'loginByMobile'])
    ->name('auth.login-by-mobile');

Route::post('/auth/register-by-mobile', [MobileAuthController::class, 'registerByMobile'])
    ->name('auth.register-by-mobile');

require __DIR__.'/auth.php';
