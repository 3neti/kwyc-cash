<?php

use App\Middleware\ContinueRegistrationMiddleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

describe('ContinueRegistrationMiddleware', function () {
    beforeEach(function () {
        Cache::flush();
        User::query()->delete();
    });

    it('prompts for email if user email matches app domain', function () {
        $middleware = new ContinueRegistrationMiddleware();
        $from = '09171234567';

        $user = User::factory()->create([
            'mobile' => $from,
            'email' => '09171234567@' . parse_url(config('app.url'), PHP_URL_HOST),
        ]);

        $response = $middleware->handle('hello', $from, '2158', fn() => null);

        expect($response)->toBeInstanceOf(JsonResponse::class);
        expect($response->getData(true)['message'])->toContain('Please reply with your email address');
    });

    it('accepts valid reply and updates email', function () {
        $middleware = new ContinueRegistrationMiddleware();
        $from = '09181234567';

        $user = User::factory()->create([
            'mobile' => $from,
            'email' => '09181234567@' . parse_url(config('app.url'), PHP_URL_HOST),
        ]);

        Cache::put("pending_email:{$from}", true);

        $response = $middleware->handle('real.email@example.com', $from, '2158', fn() => null);

        $user->refresh();

        expect($user->email)->toBe('real.email@example.com');
        expect($response->getData(true)['message'])->toContain('fully registered');
    });

    it('rejects invalid email replies', function () {
        $middleware = new ContinueRegistrationMiddleware();
        $from = '09181234567';
        $defaultEmail = $from . '@' . strtolower(parse_url(config('app.url'), PHP_URL_HOST));

        $user = User::factory()->create([
            'mobile' => $from,
            'email' => $defaultEmail,
        ]);

        Cache::put("pending_email:{$from}", true);

        $response = $middleware->handle('invalid-email', $from, '2158', fn() => response()->json(['message' => 'next called']));

        expect($response)->toBeInstanceOf(Illuminate\Http\JsonResponse::class);

        $data = $response->getData(true);
        expect($data['message'])->toContain("doesn't look like a valid");
        expect($user->fresh()->email)->toBe($defaultEmail);
    });

    it('ignores users with proper email', function () {
        $middleware = new ContinueRegistrationMiddleware();
        $from = '09192345678';

        User::factory()->create([
            'mobile' => $from,
            'email' => 'valid@example.com',
        ]);

        $response = $middleware->handle('anything', $from, '2158', fn() => 'PASSTHROUGH');

        expect($response)->toBe('PASSTHROUGH');
    });
});
