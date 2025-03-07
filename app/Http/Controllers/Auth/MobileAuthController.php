<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\User;

class MobileAuthController extends Controller
{

    public function loginByMobile(Request $request): \Illuminate\Http\JsonResponse
    {
        $mobile = $request->get('mobile');

        // Find the user by mobile number
        $user = User::where('mobile', $mobile)->first();

        if ($user) {
            // Log the user in without a password
            Auth::login($user);

            // Regenerate session to prevent session fixation attacks
            $request->session()->regenerate();

            Log::info("User with mobile {$mobile} logged in via wallet deposit.");

            // Directly redirect to the campaign creation page
            return response()->json([
                'message' => 'Logged in successfully via wallet deposit.',
                'redirect' => route('campaign.create', absolute: false)
            ]);
        }

        return response()->json([
            'error' => 'No account associated with this mobile number.'
        ], 404);
    }
}
