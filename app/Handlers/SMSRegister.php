<?php

namespace App\Handlers;

use Propaganistas\LaravelPhone\Rules\Phone;
use Illuminate\Support\Facades\Validator;
use App\Contracts\SMSHandlerInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use App\Models\User;

class SMSRegister implements SMSHandlerInterface
{
    /**
     * Handle SMS user registration.
     *
     * Expected syntax:
     * REGISTER {email} {mobile} [--name|-n "Full Name"] [--password|-p "Secret"]
     *
     * Example:
     * REGISTER john@doe.com 09171234567 -n"Juan Dela Cruz" -p"Secret123"
     */
    public function __invoke(array $values, string $from, string $to): JsonResponse
    {
        $extra = $values['extra'] ?? '';

        Log::info('📨 Processing SMS registration', [
            'from' => $from,
            'to' => $to,
            'email' => $values['email'] ?? null,
            'mobile' => $values['mobile'] ?? null,
            'extra' => $extra,
        ]);

        // Parse extras and merge
        $extras = $this->parseExtras($extra);
        Log::debug('📦 Parsed extras from SMS', $extras);
        $values = array_merge($values, $extras);

        $rules = [
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'mobile' => ['required', (new Phone)->type('mobile')->country('PH'), Rule::unique(User::class)],
            'name' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', Rules\Password::defaults()],
        ];

        try {
            $validated = Validator::make($values, $rules)->validate();

            $appDomain = parse_url(config('app.url'), PHP_URL_HOST);
            $email = $validated['email'];
            $mobile = $validated['mobile'];
            $name = $validated['name'] ?? "{$mobile}@{$appDomain}";
            $password = $validated['password'] ?? 'password';

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'mobile' => $mobile,
                'password' => Hash::make($password),
            ]);

            Log::info('✅ User registered successfully', [
                'id' => $user->id,
                'email' => $user->email,
                'mobile' => $user->mobile,
            ]);

            return response()->json([
                'message' => "Registered: {$user->name} <{$user->email}>"
            ]);
        } catch (\Throwable $th) {
            Log::error('❌ SMS registration failed', [
                'exception' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            report($th);

            return response()->json([
                'message' => "Syntax: REGISTER email mobile [-n\"Full Name\"] [-p\"Password\"]",
            ]);
        }
    }

    /**
     * Parse --key or -k style parameters from extra.
     * Supports quoted or unquoted values for: --name / -n, --password / -p
     */
    protected function parseExtras(string $extra): array
    {
        $results = [
            'name' => null,
            'password' => null,
        ];

        // Match long/short flags with quoted or unquoted values
        preg_match_all('/(?:--(?P<long>\w+)|-(?P<short>\w))\s*(?:"([^"]+)"|(\S+))/', $extra, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $key = $match['long'] ?: $match['short'];
            $value = $match[3] ?? $match[4];

            Log::debug('🔍 Found flag', ['key' => $key, 'value' => $value]);

            match ($key) {
                'n', 'name' => $results['name'] = trim($value),
                'p', 'password' => $results['password'] = trim($value),
                default => null,
            };
        }

        return array_filter($results);
    }
}
