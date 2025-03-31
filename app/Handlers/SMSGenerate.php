<?php

namespace App\Handlers;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Notification;
use FrittenKeeZ\Vouchers\Models\Voucher;
use App\Contracts\SMSHandlerInterface;
use App\Actions\GenerateCashVouchers;
use App\Notifications\SMSAutoReply;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use App\Models\User;

class SMSGenerate implements SMSHandlerInterface
{
    /**
     * Handle incoming SMS messages and generate cash vouchers.
     *
     * @param array $values The validated data from the SMS message.
     * @param string $from The sender's mobile number.
     * @param string $to The recipient's mobile number.
     *
     * @return JsonResponse A JSON response containing the generated voucher codes or an error message.
     * @throws ValidationException
     */
    public function __invoke(array $values, string $from, string $to): JsonResponse
    {
        // Ensure 'extra' is set with a default value of '' if not provided.
        $values['extra'] = $values['extra'] ?? '';

        // Parse extra field
        $extraParsed = $this->parseExtra($values['extra']);
        $values = array_merge($values, $extraParsed);

        // Instantiate the action class responsible for generating cash vouchers.
        $action = app(GenerateCashVouchers::class);

        // Validate incoming SMS data using the rules defined in the action class.
        $validated = validator($values, $action->rules())->validate();

        // Find the user associated with the sender's mobile number.
        if ($origin = User::where('mobile', $from)->first()) {
            // Generate vouchers and extract their codes.
            $vouchers = $action->run($origin, $validated);

            $total = $vouchers->count();
            $voucherCodes = $vouchers
                ->take(10)
                ->map(fn (Voucher $voucher) => $voucher->code)
                ->implode(',');
            $voucherCodes .= $total > 10 ? '…' : '';

            // Prepare the response data.
            $data = [
                'codes'  => $voucherCodes,
                'amount' => $values['value'],
                'duration' => $values['duration'],
                'feedback' => $values['feedback'],
                'tag' => $values['tag'],
                'dedication' => $values['dedication'],
            ];

            // Format the auto-reply message.
            $reply = collect($data)
                ->filter() // remove null/empty fields
                ->map(fn($v, $k) => ucfirst($k) . ': ' . $v)
                ->implode("\n");

            // Send the auto-reply via notification.
            Notification::route('engage_spark', $from)
                ->notify(new SMSAutoReply($reply));

            // Return the generated voucher data as a JSON response.
            return response()->json([
                'message' => $reply
            ]);
        }

        // Return an error response if the sender is not recognized.
        return response()->json([
            'message' => 'Something is wrong',
        ]);
    }

    /**
     * Extracts parameters from free-form `extra` SMS input.
     * Supports: $<amount>, *<qty>, !<duration>, @<feedback>, #<tag>, and the rest as dedication text.
     */
    private function parseExtra(string $extra): array
    {
        $rawValue = null;
        $rawQty = null;
        $rawDuration = null;
        $rawFeedback = null;
        $rawTag = null;
        $rawDedication = [];

        // Split by space
        $parts = preg_split('/\s+/', $extra);

        foreach ($parts as $part) {
            if (str_starts_with($part, '$')) {
                $rawValue = ltrim($part, '$');
            } elseif (str_starts_with($part, '*')) {
                $rawQty = ltrim($part, '*');
            } elseif (str_starts_with($part, '!')) {
                $rawDuration = ltrim($part, '!');
            } elseif (str_starts_with($part, '@')) {
                $rawFeedback = ltrim($part, '@');
            } elseif (str_starts_with($part, '#')) {
                $rawTag = ltrim($part, '#');
            } else {
                $rawDedication[] = $part;
            }
        }

        return [
            'value' => $rawValue ? max(50, (int) $rawValue) : 50,
            'qty' => $rawQty ? max(1, (int) $rawQty) : 1,
            'duration' => Str::normalizeDuration($rawDuration),
            'feedback' => $rawFeedback && Str::isMobileNumber($rawFeedback) ? Str::formatMobileNumber($rawFeedback) : null,
            'tag' => $rawTag,
            'dedication' => implode(' ', $rawDedication),
        ];
    }
}
