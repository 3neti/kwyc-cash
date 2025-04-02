<?php

use App\Actions\SendRegistrationFeedback;
use App\Services\OmniChannelService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use App\Models\User;

it('sends personalized SMS to the registered user', function () {
    // Fake OmniChannelService
    $mockSms = Mockery::mock(OmniChannelService::class);
    App::instance(OmniChannelService::class, $mockSms);

    $user = User::factory()->make([
        'name' => 'Test User',
        'mobile' => '09171234567',
    ]);

    $expectedMessage = "Hello Test User! You've been registered in our system. Please keep this number safe for future logins.";

    $mockSms->shouldReceive('send')
        ->once()
        ->with($user->mobile, $expectedMessage);

    SendRegistrationFeedback::run($user);
});
