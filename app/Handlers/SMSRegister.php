<?php

namespace App\Handlers;

use Propaganistas\LaravelPhone\Rules\Phone;
use App\Actions\SendRegistrationFeedback;
use Illuminate\Support\Facades\Validator;
use App\Contracts\SMSHandlerInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use App\Events\RegisteredViaSMS;
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

        $extras = $this->parseExtras($extra);
        Log::debug('📦 Parsed extras from SMS', $extras);
        $values = array_merge($values, $extras);

        $rules = [
            'mobile' => ['required', (new Phone)->type('mobile')->country('PH'), Rule::unique(User::class)],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'name' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', Rules\Password::defaults()],
        ];

        try {
            $validated = Validator::make($values, $rules)->validate();

            $appDomain = strtolower(parse_url(config('app.url'), PHP_URL_HOST));
            $mobile = $validated['mobile'];
            $email = $validated['email'] ?? "{$mobile}@{$appDomain}";
            $name = $validated['name'] ?? $email;
            $password = $validated['password'] ?? 'password';

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'mobile' => $mobile,
                'password' => Hash::make($password),
            ]);

            RegisteredViaSMS::dispatch($user);

            Log::info('✅ User registered successfully', [
                'id' => $user->id,
                'email' => $user->email,
                'mobile' => $user->mobile,
            ]);

            // Determine if this is self-registration or third-party
            $selfRegistration = $from === $mobile;

            if ($selfRegistration) {
                return response()->json([
                    'message' => "Registered: {$user->name} <{$user->email}>"
                ]);
            }

            // Notify the registered user via SMS
            SendRegistrationFeedback::run($user);

            return response()->json([
                'message' => "Registration complete. We've notified {$user->mobile} with their account details."
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
     * Supports quoted or unquoted values for: --email / -e, --name / -n, --password / -p
     */
    protected function parseExtras(string $extra): array
    {
        $results = [
            'email' => null,
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
                'e', 'email' => $results['email'] = trim($value),
                'n', 'name' => $results['name'] = trim($value),
                'p', 'password' => $results['password'] = trim($value),
                default => null,
            };
        }

        return array_filter($results);
    }
}
