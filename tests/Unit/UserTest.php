<?php

use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};
use Illuminate\Database\Eloquent\Collection;
use App\Models\{Campaign, Cash, User};

uses(RefreshDatabase::class, WithFaker::class);

test('user has mobile', function () {
    $user = User::factory()->create(['mobile' => '09171234567']);
    expect($user->mobile)->toBe('09171234567');
    expect($user->country)->toBe('PH');
});

test('user has many campaigns relation', function () {
    // Create a user with 3 campaigns
    $user = User::factory()->has(Campaign::factory()->count(3))->create();

    // Ensure campaigns relation returns a Collection
    expect($user->campaigns)->toBeInstanceOf(Collection::class);

    // Ensure exactly 3 campaigns are created
    expect($user->campaigns)->toHaveCount(3);

    // Ensure the campaigns belong to the same user
    foreach ($user->campaigns as $campaign) {
        expect($campaign->user_id)->toBe($user->id);
    }

    // Ensure currentCampaign is initially null
    expect($user->currentCampaign)->toBeNull();

    // Pick a campaign and assign it as the current campaign
    $campaign = $user->campaigns->first();
    $user->currentCampaign = $campaign;
    $user->save();

    // Ensure currentCampaign is correctly set
    expect($user->currentCampaign->is($campaign))->toBeTrue();
});
