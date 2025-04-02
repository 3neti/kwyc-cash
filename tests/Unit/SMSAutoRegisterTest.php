<?php

use App\Handlers\SMSAutoRegister;
use App\Handlers\SMSRegister;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Illuminate\Http\JsonResponse;
use App\Events\RegisteredViaSMS;

describe('SMSAutoRegister Handler', function () {

    beforeEach(function () {
        Event::fake([RegisteredViaSMS::class]);
        User::query()->delete();
    });

    it('registers a new user with only email', function () {
        $handler = new SMSAutoRegister();

        $response = $handler([
            'email' => 'auto@example.com',
            'extra' => ''
        ], '09170001111', '2158');

        $user = User::where('email', 'auto@example.com')->first();

        expect($response)->toBeInstanceOf(JsonResponse::class);
        expect($user)->not->toBeNull();
        expect($user->mobile)->toBe('09170001111');
        expect($user->email)->toBe('auto@example.com');
        Event::assertDispatched(RegisteredViaSMS::class, function ($event) use ($user) {
            return $event->user->is($user);
        });
    });

    it('registers with email, name, and password from extras', function () {
        $handler = new SMSAutoRegister();

        $response = $handler([
            'email' => 'full@example.com',
            'extra' => '-n"Full Auto User" -p"Secure1234"'
        ], '09179998888', '2158');

        $user = User::where('email', 'full@example.com')->first();

        expect($user)->not->toBeNull();
        expect($user->name)->toBe('Full Auto User');
        expect(Hash::check('Secure1234', $user->password))->toBeTrue();
    });

    it('fails if email is missing', function () {
        $handler = new SMSAutoRegister();

        $response = rescue(function () use ($handler) {
            return $handler([
                'extra' => '-n"No Email" -p"Fail123"'
            ], '09175556666', '2158');
        }, fn ($e) => $e);

        expect($response)->toBeInstanceOf(\Illuminate\Validation\ValidationException::class);
        expect($response->errors())->toHaveKey('email');
    });

    it('fails if email is already used', function () {
        User::factory()->create([
            'email' => 'used@example.com',
            'mobile' => '09998887777',
        ]);

        $handler = new SMSAutoRegister();

        $response = rescue(function () use ($handler) {
            return $handler([
                'email' => 'used@example.com',
                'extra' => '-n"Duplicate"'
            ], '09175556666', '2158');
        }, fn ($e) => $e);

        expect($response)->toBeInstanceOf(\Illuminate\Validation\ValidationException::class);
        expect($response->errors())->toHaveKey('email');
    });

});
