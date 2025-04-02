<?php

namespace App\Handlers;

use Illuminate\Support\Facades\Validator;
use App\Contracts\SMSHandlerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use App\Models\User;

class SMSAutoRegister implements SMSHandlerInterface
{
    /**
     * Handle auto-registration via SMS.
     *
     * Expected syntax:
     * REG {email} [--name|-n "Full Name"] [--password|-p "Secret"]
     *
     * Example:
     * REG someone@example.com -n"Self Register" -p"MySecret"
     */
    public function __invoke(array $values, string $from, string $to): JsonResponse
    {
        // Ensure email is provided and valid
        Validator::validate($values, [
            'email' => ['required', 'string', 'email', 'lowercase',  Rule::unique(User::class)],
        ]);

        // Set mobile number to sender's number
        $values['mobile'] = $from;

        // Reuse SMSRegister handler
        return app(SMSRegister::class)->__invoke($values, $from, $to);
    }
}
