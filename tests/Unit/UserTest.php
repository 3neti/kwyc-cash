<?php

use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};
use App\Models\{Cash, User};

uses(RefreshDatabase::class, WithFaker::class);

test('user can deposit', function () {
    $user = User::factory()->create();
    $user->depositFloat(100);
    expect((float) $user->balanceFloat)->toBe(100.0);
})->skip();

test('user has mobile', function () {
    $user = User::factory()->create(['mobile' => '09171234567']);
    expect($user->mobile)->toBe('09171234567');
    expect($user->country)->toBe('PH');
});
