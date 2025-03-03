<?php

use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};
use FrittenKeeZ\Vouchers\Facades\Vouchers;
use FrittenKeeZ\Vouchers\Models\Voucher;
use App\Models\{Cash, Contact, User};
use App\Data\VoucherData;
use App\Actions\DisburseAmount;
use Illuminate\Support\Facades\Http;

use Mockery\MockInterface;

uses(RefreshDatabase::class, WithFaker::class);

beforeEach(function () {
    // Mock the DisburseAmount action to always return true
    $this->disburseMock = Mockery::mock(DisburseAmount::class, function (MockInterface $mock) {
        $mock->shouldReceive('handle')->andReturn(true);
//        $mock->shouldReceive('disburse')->andReturn(true);
    });

    $this->app->instance(DisburseAmount::class, $this->disburseMock);
});

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

//    Http::fake([
//        config('kwyc-cash.disbursement.server.url') => Http::response(
//            ['uuid' => (string) Str::uuid()],
//            200,
//            ['Content-Type' => 'application/json']
//        ),
//    ]);

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
