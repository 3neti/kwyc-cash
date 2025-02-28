<?php

use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};
use FrittenKeeZ\Vouchers\Models\Voucher;
use App\Actions\GenerateCashVouchers;
use App\Models\{Cash, User};

uses(RefreshDatabase::class, WithFaker::class);

it('allows a user to generate multiple cash vouchers', function () {
    $user = User::factory()->create();

    $params = [
        'qty' => 3,
        'value' => 500,
    ];

    // Ensure there are no cash instances
    expect(Cash::count())->toBe(0);

    // Ensure there are no vouchers
    expect(Voucher::count())->toBe(0);

    $vouchers = GenerateCashVouchers::run($user, $params);

    // Ensure we received exactly 3 vouchers
    expect($vouchers)->toHaveCount(3);

    // Ensure the cash instances were created
    expect(Cash::count())->toBe(3);

    // Ensure the vouchers were created
    expect(Voucher::count())->toBe(3);

    // Ensure each voucher has a corresponding cash entity and user
    foreach ($vouchers as $voucher) {
        expect($cash = $voucher->getEntities(Cash::class)->first())->not->toBeNull();
        expect($cash->user->is($user))->toBeTrue();
        expect($voucher->owner->is($user))->toBeTrue();
    }
});
