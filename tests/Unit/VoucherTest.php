<?php

use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};
use FrittenKeeZ\Vouchers\Facades\Vouchers;
use FrittenKeeZ\Vouchers\Models\Voucher;
use App\Models\{Cash, Contact, User};
use App\Data\VoucherData;

uses(RefreshDatabase::class, WithFaker::class);

test('voucher works', function () {
    $user = User::factory()->create();
    $cash = Cash::factory()->create();
    $contact = Contact::factory()->create();
    $user->assignCash($cash);
    $entities = compact('cash');
    expect($user->vouchers)->toHaveCount(0);
    $voucher = Vouchers::withOwner($user)->withEntities(...$entities)->create();
    $code = $voucher->code;
    if ($voucher instanceof Voucher) {
        expect($voucher->getEntities(Cash::class)->first()->is($cash))->toBeTrue();
    }
    $success = Vouchers::redeem($code, $contact);
    expect($success)->toBeTrue();
    $success = Vouchers::redeemable($code);
    expect($success)->toBeFalse();
    $user->refresh();
    expect($user->vouchers)->toHaveCount(1);
    $voucher->refresh();
    expect($voucher->redeemers->first()->redeemer->is($contact))->toBeTrue();
});

test('voucher has data', function () {
    $user = User::factory()->create();
    $cash = Cash::factory()->create(['value' => 537, 'tag' => 'AA']);
    $user->assignCash($cash);
    $entities = compact('cash');
    $voucher = Vouchers::withEntities(...$entities)->create();
    $data = VoucherData::fromModel($voucher);
    expect($data->code)->toBe($voucher->code);
    expect($data->amount)->toBe(537.0);
});
