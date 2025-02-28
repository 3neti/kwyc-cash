<?php

use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};
use FrittenKeeZ\Vouchers\Facades\Vouchers;
use FrittenKeeZ\Vouchers\Models\Voucher;
use App\Models\{Cash, User};

uses(RefreshDatabase::class, WithFaker::class);

test('example', function () {
    $user = User::factory()->create();
    $user->depositFloat(100);
    expect((float) $user->balanceFloat)->toBe(100.0);
});

it('allows a user to attach cash', function () {
    $user = User::factory()->create();
    $cash = Cash::factory()->create();

    // Attach cash to user
    $user->cashes()->attach($cash->id);

    // Assert that the pivot table has the correct entry
    expect(\DB::table('cash_user')->where([
        'user_id' => $user->id,
        'cash_id' => $cash->id,
    ])->exists())->toBeTrue();
});

it('allows a user to detach cash', function () {
    $user = User::factory()->create();
    $cash = Cash::factory()->create();

    // Attach and then detach cash
    $user->cashes()->attach($cash->id);
    $user->cashes()->detach($cash->id);

    // Assert that the record is deleted from pivot table
    expect(\DB::table('cash_user')->where([
        'user_id' => $user->id,
        'cash_id' => $cash->id,
    ])->exists())->toBeFalse();
});

it('allows cash to belong to multiple users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $cash = Cash::factory()->create();

    // Attach the same cash instance to two users
    $user1->cashes()->attach($cash->id);
    $user2->cashes()->attach($cash->id);

    // Assert that both users have the cash
    expect(\DB::table('cash_user')->where([
        'user_id' => $user1->id,
        'cash_id' => $cash->id,
    ])->exists())->toBeTrue();

    expect(\DB::table('cash_user')->where([
        'user_id' => $user2->id,
        'cash_id' => $cash->id,
    ])->exists())->toBeTrue();
});

it('retrieves the correct cash instances for a user', function () {
    $user = User::factory()->create();
    $cash = Cash::factory()->create();

    // Attach cash to user
    $user->cashes()->attach($cash->id);

    // Reload user model to fetch relationships
    $user->refresh();

    // Expect that the user has the cash instance
    expect($user->cashes)->toHaveCount(1);
    expect($user->cashes->first()->id)->toBe($cash->id);
});

it('allows a user to own multiple cash instances', function () {
    $user = User::factory()->create();
    $user->depositFloat(100000);
    $cash1 = Cash::factory()->create();
    $cash2 = Cash::factory()->create();

    // Assign cash instances to user
    $user->assignCash($cash1);
    $user->assignCash($cash2);

    // Reload relationships
    $user->refresh();

    expect($user->cashes)->toHaveCount(2);
    expect($user->cashes->pluck('id'))->toContain($cash1->id, $cash2->id);
});

it('ensures a cash instance belongs to only one user', function () {
    $user1 = User::factory()->create();
    $user1->depositFloat(100000);
    $user2 = User::factory()->create();
    $user2->depositFloat(100000);
    $cash = Cash::factory()->create();

    // Assign the cash to user1
    $user1->assignCash($cash);

    // Try assigning it to user2 (should remove from user1)
    $user2->assignCash($cash);

    // Reload relationships
    $cash->refresh();

    expect($cash->users)->toHaveCount(1); // Only one user should be attached
    expect($cash->users()->first()->id)->toBe($user2->id); // Should be reassigned to user2
});

it('ensures cash_user table only contains unique cash-user pairs', function () {
    $user = User::factory()->create(); $user->depositFloat(1000000);
    $cash = Cash::factory()->create();

    // Assign cash to user multiple times
    $user->assignCash($cash);
    $user->assignCash($cash);

    // Query database directly
    $count = \DB::table('cash_user')->where('cash_id', $cash->id)->count();

    // There should be only ONE record per cash_id
    expect($count)->toBe(1);
});
