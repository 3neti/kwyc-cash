<?php

use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};
use FrittenKeeZ\Vouchers\Models\Voucher;
use App\Actions\GenerateCashVouchers;
use App\Actions\RedeemCashVoucher;
use App\Models\{Cash, User};

uses(RefreshDatabase::class, WithFaker::class);

test('allows a contact to redeem a voucher code', function () {
    $user = User::factory()->create();
    $user->depositFloat(10000);

    $params = [
        'qty' => 3,
        'value' => 25,
        'tag' => 'AA537'
    ];
    $vouchers = GenerateCashVouchers::run($user, $params);
    $voucher = $vouchers->first();

    $voucher_code = $voucher->code;
    $mobile = '09173011987';

    $response = RedeemCashVoucher::run($voucher_code, $mobile);
    expect($response)->toBeTrue();
});
