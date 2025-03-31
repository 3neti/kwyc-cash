<?php

namespace App\Handlers;

use App\Actions\{AttachVoucherToMobile, GenerateCashVouchers};
use Illuminate\Validation\ValidationException;
use FrittenKeeZ\Vouchers\Models\Voucher;
use App\Contracts\SMSHandlerInterface;
use Illuminate\Support\{Arr, Str};
use Illuminate\Http\JsonResponse;
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

        // Extract mobile if present
        $mobile_to_attach = Arr::pull($extraParsed, 'mobile');

        $values = array_merge($values, $extraParsed);

        // Instantiate the action class responsible for generating cash vouchers.
        $action = app(GenerateCashVouchers::class);

        // Validate incoming SMS data using the rules defined in the action class.
        $validated = validator($values, $action->rules())->validate();

        // Find the user associated with the sender's mobile number.
        if ($origin = User::where('mobile', $from)->first()) {
            // Generate vouchers and extract their codes.
            $vouchers = $action->run($origin, $validated);

            // Attach each generated voucher to the provided mobile (if any)
            if ($mobile_to_attach) {
                foreach ($vouchers as $voucher) {
                    AttachVoucherToMobile::run([
                        'mobile' => $mobile_to_attach,
                        'voucher_code' => $voucher->code,
                    ]);
                }
            }

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
                'mobile' => $mobile_to_attach,
                'dedication' => $values['dedication'],
            ];

            // Format the auto-reply message.
            $reply = collect($data)
                ->filter() // remove null/empty fields
                ->map(fn($v, $k) => ucfirst($k) . ': ' . $v)
                ->implode("\n");

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
     *
     * Prefixes supported:
     *
     * Symbol          | Purpose                   | Example                        | Parsed As
     * ----------------|---------------------------|--------------------------------|---------------------
     * `$` / `₱`       | Voucher value             | ₱200                           | `value = 200`
     * `*`             | Quantity                  | *5                             | `qty = 5`
     * `!`             | Expiry duration (ISO 8601)| !PT2H / !2H                    | `duration = PT2H`
     * `@`             | Feedback recipient        | @09171234567                   | `feedback = mobile`
     * `#`             | Tag or category           | #ReliefAid                     | `tag = ReliefAid`
     * `&` / `>` / `:` | Attach vouchers to mobile | &09171234567 / >0917… / :0917… | `mobile = recipient`
     *
     * All other segments are considered part of the dedication message.
     *
     * @param string $extra The raw input string from the SMS message.
     * @return array An array of parsed SMS parameters.
     */
    private function parseExtra(string $extra): array
    {
        $rawValue = null;
        $rawQty = null;
        $rawDuration = null;
        $rawFeedback = null;
        $rawTag = null;
        $rawDedication = [];
        $rawMobile = null;

        // Split by space
        $parts = preg_split('/\s+/', $extra);

        foreach ($parts as $part) {
            if (str_starts_with($part, '$') || str_starts_with($part, '₱')) {
                $rawValue = ltrim($part, '$₱');
            } elseif (str_starts_with($part, '*')) {
                $rawQty = ltrim($part, '*');
            } elseif (str_starts_with($part, '!')) {
                $rawDuration = ltrim($part, '!');
            } elseif (str_starts_with($part, '@')) {
                $rawFeedback = ltrim($part, '@');
            } elseif (str_starts_with($part, '#')) {
                $rawTag = ltrim($part, '#');
            } elseif (
                str_starts_with($part, '&') ||
                str_starts_with($part, '>') ||
                str_starts_with($part, ':')
            ) {
                $rawMobile = ltrim($part, '&>:');
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
            'mobile' => $rawMobile && Str::isMobileNumber($rawMobile) ? Str::formatMobileNumber($rawMobile) : null,
            'dedication' => implode(' ', $rawDedication),
        ];
    }
}
