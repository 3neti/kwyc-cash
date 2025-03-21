<?php

namespace App\Handlers;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Notification;
use FrittenKeeZ\Vouchers\Models\Voucher;
use App\Contracts\SMSHandlerInterface;
use App\Actions\GenerateCashVouchers;
use App\Notifications\SMSAutoReply;
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
        // Ensure 'qty' is set with a default value of 1 if not provided.
        $values['qty'] = $values['qty'] ?? 1;

        // Ensure 'tag' is set with a default value of '' if not provided.
        $values['duration'] = $values['PT12H'] ?? '';

        // Ensure 'tag' is set with a default value of '' if not provided.
        $values['tag'] = $values['tag'] ?? '';

        // Instantiate the action class responsible for generating cash vouchers.
        $action = app(GenerateCashVouchers::class);

        // Validate incoming SMS data using the rules defined in the action class.
        $validated = validator($values, $action->rules())->validate();

        // Find the user associated with the sender's mobile number.
        if ($origin = User::where('mobile', $from)->first()) {
            // Generate vouchers and extract their codes.
            $vouchers = $action->run($origin, $validated);

            $voucherCodes = $vouchers
                ->map(fn (Voucher $voucher) => $voucher->code)
                ->implode(',');

            // Prepare the response data.
            $data = [
                'codes'  => $voucherCodes,
                'amount' => $values['value'],
                'duration' => $values['duration'],
                'tag' => $values['tag'],
            ];

            // Format the auto-reply message.
            $reply = __("Amount: :amount\nCodes: :codes", $data);

            // Send the auto-reply via notification.
            Notification::route('engage_spark', $from)
                ->notify(new SMSAutoReply($reply));

            // Return the generated voucher data as a JSON response.
            return response()->json($data);
        }

        // Return an error response if the sender is not recognized.
        return response()->json([
            'message' => 'Something is wrong',
        ]);
    }
}
