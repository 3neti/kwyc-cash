<?php

use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};
use App\Models\{Campaign, User};

uses(RefreshDatabase::class, WithFaker::class);

test('campaign has uuid', function () {
    $campaign = Campaign::factory()->create();
    expect($campaign->id)->toBeUuid();
});

test('campaign has user relation', function () {
    $campaign = Campaign::factory()->forUser()->create();
    expect($campaign->user)->toBeInstanceOf(User::class);
});

test('campaign has a default name', function () {
    $campaign = Campaign::factory()->forUser()->create();
    $user_id = is_null($campaign->user) ? 'x' : $campaign->user->id;
    $name = $user_id . '-' .substr(strrchr($campaign->id, '-'), 1);
    expect($campaign->name)->toBe($name);
});

test('campaign has attributes', function () {
    $campaign = Campaign::factory()->create();
    expect($campaign->inputs)->toBeArray();
    expect($campaign->rider)->toBeString();
    expect($campaign->reference_label)->toBeString();
    expect($campaign->dedication)->toBeString();
});

test('campaign has url', function () {
    $campaign = Campaign::factory()->create();
    expect($campaign->url)->toBeUrl();
//    dd($campaign->QRCodeURI);
});

test('campaign has dated statuses', function () {
    $campaign = Campaign::factory()->create();
    expect($campaign->disabled)->toBeFalse();
    $campaign->disabled = true;
    expect($campaign->disabled)->toBeTrue();
});

test('campaign has meta', function () {
    $campaign = Campaign::factory()->create();
    $campaign->meta = [
        'rey' => ['side' => 'light'],
        'snoke' => ['side' => 'dark'],
    ];
    $campaign->meta->set('rey.side', 'dark');
    expect($campaign->meta->get('rey.side'))->toBe('dark');
});
