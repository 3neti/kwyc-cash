<?php

namespace App\Handlers;

use Propaganistas\LaravelPhone\Rules\Phone;
use App\Contracts\SMSHandlerInterface;
use Illuminate\Http\JsonResponse;
use App\Models\User;

class SMSTransfer implements SMSHandlerInterface
{
    /**
     * Handle SMS message logging as an invokable class.
     */
    public function __invoke(array $values, string $from, string $to): JsonResponse
    {
        $validated = validator($values, [
            'mobile' => ['required', (new Phone)->type('mobile')->country('PH')],
            'amount' => ['required', 'int', 'min:50']
        ])->validate();

        if ($origin = User::where('mobile', $from)->first()) {
            if ($destination = User::where('mobile', $validated['mobile'])->first()) {
                $transfer = $origin->transferFloat($destination, $validated['amount']);

                return response()->json([
                    'mobile' => $values['mobile'],
                    'amount' => $values['amount'],
                ]);
            }
        }

        return response()->json([
            'message' => 'Something is wrong',
        ]);
    }
}
