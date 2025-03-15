<?php

use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};
use App\Models\User;

uses(RefreshDatabase::class, WithFaker::class);

test('user can deposit', function () {
    $user = User::factory()->create();
    $user->depositFloat(100);
    expect((float) $user->balanceFloat)->toBe(100.0);
});

test('user can transfer', function () {
    [$user1, $user2] = User::factory(2)->create();
    $user1->depositFloat(100);
    expect((float) $user2->balanceFloat)->toBe(0.0);
    $user1->transferFloat($user2, 25);
    expect((float) $user1->balanceFloat)->toBe(75.0);
    expect((float) $user2->balanceFloat)->toBe(25.0);
});
