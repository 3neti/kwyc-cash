<?php

use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};
use App\Models\{Cash, User};

uses(RefreshDatabase::class, WithFaker::class);

test('example', function () {
    $user = User::factory()->create();
    $user->depositFloat(100);
    expect((float) $user->balanceFloat)->toBe(100.0);
})->skip();
