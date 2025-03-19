<?php

namespace App\Handlers;

use App\Contracts\SMSHandlerInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class SMSLog implements SMSHandlerInterface
{
    /**
     * Handle SMS message logging as an invokable class.
     */
    public function __invoke(array $values, string $from, string $to): JsonResponse
    {
        Log::info("Logging SMS Message", [
            'message' => $values['message'],
            'from' => $from,
            'to' => $to,
        ]);

        return response()->json([
            'message' => "Logged: " . $values['message'],
            'from' => $from,
            'to' => $to,
        ]);
    }
}
