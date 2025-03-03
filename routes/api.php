<?php

use App\Http\Controllers\API\RedeemCashVoucherController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/vouchers/redeem', [RedeemCashVoucherController::class, 'redeem'])->name('api.vouchers.redeem');
Route::get('/vouchers/{voucherCode}/status', [RedeemCashVoucherController::class, 'status'])->name('api.vouchers.status');
