<?php

namespace App\Http\Controllers\Auth;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Illuminate\Support\Facades\{Auth, Hash, Log};
use App\Http\Controllers\Controller;
use App\Events\DepositConfirmed;
use Illuminate\Http\Request;
use App\Models\User;

class MobileAuthController extends Controller
{
    protected string $redirect = 'vouchers.create';

    /**
     * Login an existing user by mobile number.
     */
    public function loginByMobile(Request $request): \Illuminate\Http\JsonResponse
    {
        $mobile = $request->get('mobile');

        // Find the user by mobile number
        $user = User::where('mobile', $mobile)->first();

        if ($user) {
            Auth::login($user);

            // Regenerate session to prevent session fixation attacks
            $request->session()->regenerate();

            Log::info("User with mobile {$mobile} logged in via wallet deposit.");

            return response()->json([
                'message' => 'Logged in successfully via wallet deposit.',
                'redirect' => route(name: $this->redirect, absolute: false)
            ]);
        }

        return response()->json([
            'error' => 'No account associated with this mobile number.'
        ], 404);
    }

    /**
     * Automatically register a new user by mobile number.
     */
    public function registerByMobile(Request $request): \Illuminate\Http\JsonResponse
    {
        $mobile = $request->get('mobile');
        $amount = $request->get('amount', 0);
        $name = $request->get('name', $mobile);
        $appDomain = parse_url(config('app.url'), PHP_URL_HOST);

        // Create a new user with default credentials
        $user = User::create([
            'name' => $name,
            'email' => "{$mobile}@{$appDomain}",
            'mobile' => $mobile,
            'password' => Hash::make('password'), // Default password
        ]);

        Log::info("New user created for mobile {$mobile} with default credentials.");

        try {
            // Automatically deposit the specified amount to the new user's wallet
            $transaction = $user->depositFloat($amount);

            // Dispatch the deposit confirmed event
            DepositConfirmed::dispatch($user, $amount, $transaction->updated_at);

            Log::info("Initial deposit of ₱{$amount} completed for new user {$name}.");
        } catch (ExceptionInterface $e) {
            Log::error("Failed to deposit ₱{$amount} for new user {$name}: " . $e->getMessage());
        }

        // Log the user in immediately after registration
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'User registered, deposit completed, and logged in successfully.',
            'redirect' => route(name: $this->redirect, absolute: false)
        ]);
    }
}
