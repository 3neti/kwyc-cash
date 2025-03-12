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
})->name('home');

use App\Http\Middleware\CheckEmailMiddleware;

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', CheckEmailMiddleware::class])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::resource('old-redeem', RedeemCashVoucherController::class)->parameter('old-redeem', 'voucher')->only(['create', 'store', 'show']);

use App\Http\Controllers\VoucherController;
use App\Http\Controllers\WalletController;

Route::middleware(['auth', CheckEmailMiddleware::class])->group(function () {
    Route::resource('vouchers', VoucherController::class)->only('index', 'create', 'store');
    Route::resource('wallet', WalletController::class)->only('create');
    Route::resource('campaign', CampaignController::class)->only('create');
    Route::post('voucher/action', [VoucherController::class, 'handleVoucherAction'])->name('voucher.action');
});

use App\Http\Controllers\Auth\MobileAuthController;

Route::post('/auth/login-by-mobile', [MobileAuthController::class, 'loginByMobile'])
    ->name('auth.login-by-mobile');

Route::post('/auth/register-by-mobile', [MobileAuthController::class, 'registerByMobile'])
    ->name('auth.register-by-mobile');

use App\Http\Controllers\RiderController;

Route::get('rider/{voucher}', RiderController::class)->name('rider');

use App\Http\Controllers\Voucher\RedeemController;
use App\Http\Middleware\CheckVoucherMiddleware;
use App\Http\Middleware\CheckMobileMiddleware;
use App\Http\Middleware\RedeemVoucherMiddleware;
use App\Http\Middleware\SignTransactionMiddleware;

Route::resource('redeem', RedeemController::class)
    ->parameter('redeem', 'voucher')
    ->middleware([
        CheckVoucherMiddleware::class,
        CheckMobileMiddleware::class,
        SignTransactionMiddleware::class,
        RedeemVoucherMiddleware::class,
    ])
    ->only(['create', 'store', 'show'])
;

use App\Http\Controllers\SignatureController;

Route::resource('signature', SignatureController::class)
    ->only(['create', 'store']);

use App\Data\VoucherData;

Route::get('redeem-unassigned/{voucher}', function (string $voucher){
    $voucher = \FrittenKeeZ\Vouchers\Models\Voucher::where('code', $voucher)->first();
    return inertia()->render('Redeem/FailedUnassigned', [
        'voucher' => VoucherData::fromModel($voucher)
    ]);
})->name('redeem-unassigned');
require __DIR__.'/auth.php';
