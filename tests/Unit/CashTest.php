<?php

use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};
use Illuminate\Support\Facades\Hash;
use App\Data\CashData;
use App\Models\Cash;

uses(RefreshDatabase::class, WithFaker::class);


test('cash has default currency', function () {
    $cash = Cash::factory()->create();
    expect($cash->currency)->toBe('PHP');
});

test('cash is saved as centavos', function () {
    $cash = new Cash(['value' => 100]);
    $cash->save();
    expect($cash->getRawOriginal('value'))->toBe(10000);
});

test('cash value is money', function () {
    $cash = new Cash(['value' => 100]);
    expect($cash->value->getAmount()->toInt())->toBe(100);
});

test('cash has status', function () {
    $cash = Cash::factory()->create();
    expect($cash->status)->toBe(Cash::INITIAL_STATE);
    expect($cash->suspended)->toBeFalse();
    expect($cash->nullified)->toBeFalse();
    expect($cash->expired)->toBeFalse();
    $cash->suspended = true;
    expect($cash->suspended)->toBeTrue();
    $cash->nullified_at = now();
    expect($cash->nullified)->toBeTrue();
});

test('cash has data', function () {
    $cash = Cash::factory()->create(['value' => 100]);
    expect($data = $cash->getData())->toBeInstanceOf(CashData::class);
    expect($data->value)->toBe(100.0);
});

test('cash has secret', function() {
    $cash = Cash::factory()->create(['secret' => $secret = $this->faker->word()]);
    expect(Hash::check($secret, $cash->secret))->toBeTrue();
});
