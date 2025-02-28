<?php

use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};
use FrittenKeeZ\Vouchers\Facades\Vouchers;
use FrittenKeeZ\Vouchers\Models\Voucher;
use App\Models\{Cash, User};
use App\Data\VoucherData;

uses(RefreshDatabase::class, WithFaker::class);

test('voucher works', function () {
    $user = User::factory()->create();
    $cash = Cash::factory()->create();
    $entities = compact('cash');
    $voucher = Vouchers::withEntities(...$entities)->create();
    $code = $voucher->code;
    if ($voucher instanceof Voucher) {
        expect($voucher->getEntities(Cash::class)->first()->is($cash))->toBeTrue();
    }
    $success = Vouchers::redeem($code, $user);
    expect($success)->toBeTrue();
    $success = Vouchers::redeemable($code);
    expect($success)->toBeFalse();
});

test('voucher has data', function () {
    $user = User::factory()->create();
    $cash = Cash::factory()->create();
    $user->assignCash($cash);
    $entities = compact('cash');
    $voucher = Vouchers::withEntities(...$entities)->create();
//    dd(VoucherData::fromModel($voucher));
});
