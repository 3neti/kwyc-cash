<?php

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;;
use Illuminate\Http\JsonResponse;
use App\Handlers\SMSRegister;
use Illuminate\Support\Str;
use App\Models\User;

describe('SMSRegister Handler', function () {

    beforeEach(function () {
        // Refresh database if needed
        User::query()->delete();
    });

    it('registers a new user with minimal fields', function () {
        $handler = new SMSRegister();

        $response = $handler(
            [
                'email' => 'tester@example.com',
                'mobile' => '09171234567',
                'extra' => ''
            ],
            '09171234567',
            '2158'
        );

        expect($response)->toBeInstanceOf(JsonResponse::class);
        expect(User::where('email', 'tester@example.com')->exists())->toBeTrue();
    });

    it('registers a user with name and password from quoted extra', function () {
        $handler = new SMSRegister();

        $response = $handler(
            [
                'email' => 'withname@example.com',
                'mobile' => '09181234567',
                'extra' => '-n"Juan Dela Cruz" -p"SuperSecret"'
            ],
            '09181234567',
            '2158'
        );

        $user = User::where('email', 'withname@example.com')->first();

        expect($response)->toBeInstanceOf(JsonResponse::class);
        expect($user)->not->toBeNull();
        expect($user->name)->toBe('Juan Dela Cruz');
        expect(Hash::check('SuperSecret', $user->password))->toBeTrue();
    });

    it('fails gracefully and returns syntax help', function () {
        $handler = new SMSRegister();

        $response = $handler(
            [
                'email' => 'invalid-email',
                'mobile' => 'notaphone',
                'extra' => '-nInvalid'
            ],
            '1234',
            '2158'
        );

        $data = $response->getData(true);
        expect($data['message'])->toContain('Syntax: REGISTER');
    });

});
