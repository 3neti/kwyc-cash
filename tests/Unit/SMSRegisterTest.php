<?php

use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use App\Handlers\SMSRegister;
use Illuminate\Support\Str;
use App\Models\User;

describe('SMSRegister Handler', function () {

    beforeEach(function () {
        User::query()->delete();
    });

    it('registers a new user with only mobile', function () {
        $handler = new SMSRegister();

        $response = $handler(
            [
                'mobile' => '09171234567',
                'extra' => ''
            ],
            '09171234567',
            '2158'
        );

        $user = User::where('mobile', '09171234567')->first();
        $expected = '09171234567@' . strtolower(parse_url(config('app.url'), PHP_URL_HOST));

        expect($response)->toBeInstanceOf(JsonResponse::class);
        expect($user)->not->toBeNull();
        expect($user->email)->toBe($expected);
        expect(Str::lower($user->name))->toBe(Str::lower($expected));
    });

    it('registers with name and password via quoted extras', function () {
        $handler = new SMSRegister();

        $response = $handler(
            [
                'mobile' => '09181234567',
                'extra' => '-n"Juan Dela Cruz" -p"Secret123" -e"custom@email.com"'
            ],
            '09181234567',
            '2158'
        );

        $user = User::where('email', 'custom@email.com')->first();

        expect($response)->toBeInstanceOf(JsonResponse::class);
        expect($user)->not->toBeNull();
        expect($user->name)->toBe('Juan Dela Cruz');
        expect(Hash::check('Secret123', $user->password))->toBeTrue();
    });

    it('fails and provides syntax message on invalid input', function () {
        $handler = new SMSRegister();

        $response = $handler(
            [
                'mobile' => 'notaphone',
                'extra' => '-nInvalid'
            ],
            '1234',
            '2158'
        );

        $data = $response->getData(true);
        expect($data['message'])->toContain('Syntax: REGISTER');
    });

    it('prevents duplicate mobile registration', function () {
        User::create([
            'name' => 'Existing',
            'email' => 'existing@example.com',
            'mobile' => '09195556666',
            'password' => bcrypt('secret'),
        ]);

        $handler = new SMSRegister();

        $response = $handler(
            [
                'mobile' => '09195556666',
                'extra' => '-n"Another One"'
            ],
            '09195556666',
            '2158'
        );

        $data = $response->getData(true);
        expect($data['message'])->toContain('Syntax: REGISTER');
    });

    it('simulates SMS input parsing accurately', function () {
        $smsText = 'REGISTER 09191234567 -n"Simulated User" -p"Test1234" -e"sms@fake.com"';

        preg_match('/REGISTER\s+(\S+)\s+(.*)/', $smsText, $matches);
        $mobile = $matches[1];
        $extra = $matches[2];

        $handler = new SMSRegister();

        $response = $handler([
            'mobile' => $mobile,
            'extra' => $extra,
        ], $mobile, '2158');

        $user = User::where('email', 'sms@fake.com')->first();

        expect($user)->not->toBeNull();
        expect($user->name)->toBe('Simulated User');
        expect(Hash::check('Test1234', $user->password))->toBeTrue();
    });

});
