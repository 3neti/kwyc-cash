<?php

use App\Http\Controllers\{ConfirmController, DepositController, WalletController};
use App\Http\Controllers\API\RedeemCashVoucherController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/vouchers/redeem', [RedeemCashVoucherController::class, 'redeem'])->name('api.vouchers.redeem');
Route::get('/vouchers/{voucherCode}/status', [RedeemCashVoucherController::class, 'status'])->name('api.vouchers.status');
Route::get('/vouchers/{voucherCode}/show', [RedeemCashVoucherController::class, 'show'])->name('api.vouchers.show');
Route::get('deposit', DepositController::class)->name('deposit');
Route::post('confirm', ConfirmController::class)->name('confirm');
Route::get('qr-code', [WalletController::class, 'generateDepositQRCode'])->name('wallet.qr-code');
