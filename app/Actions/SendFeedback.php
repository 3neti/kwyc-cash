<?php

namespace App\Actions;

use Illuminate\Support\Facades\{Log, Notification, Validator};
use App\Notifications\DisbursementFeedback;
use Propaganistas\LaravelPhone\Rules\Phone;
use Lorisleiva\Actions\Concerns\AsAction;
use FrittenKeeZ\Vouchers\Models\Voucher;
use Illuminate\Support\Arr;
use App\Data\VoucherData;
use Exception;

class SendFeedback
{
    use AsAction;

    /**
     * Handle the action to notify feedback recipients based on a voucher's data.
     *
     * @param Voucher $voucher The voucher object containing redeemer information.
     * @return void
     * @throws Exception If notification sending fails.
     */
    public function handle(Voucher $voucher): void
    {
        try {
            // Extract feedback array from voucher's redeemer
            $feedback = Arr::get($voucher->redeemers->first()?->getAttribute('metadata'), 'feedback', []);

            if (empty($feedback)) {
                Log::info("No feedback recipients found for Voucher Code: {$voucher->code}");
                return;
            }

            // Parse and categorize feedback into respective channels
            [$emailRecipients, $smsRecipients, $webhookRecipients] = $this->getRecipients($feedback);

            // Log recipient details before sending notifications
            Log::info('Preparing to notify feedback recipients', [
                'voucher_code' => $voucher->code,
                'email_recipients' => $emailRecipients,
                'sms_recipients' => $smsRecipients,
                'webhook_recipients' => $webhookRecipients,
            ]);

            // Send notifications to the categorized recipients
            Notification::route('mail', $emailRecipients)
                ->route('engage_spark', $smsRecipients)
                ->route('webhook', $webhookRecipients)
                ->notify(new DisbursementFeedback(VoucherData::fromModel($voucher)));

            Log::info("Notifications sent successfully for Voucher Code: {$voucher->code}");

        } catch (Exception $e) {
            // Log and rethrow exception for higher-level handling
            Log::error('Failed to send notifications for feedback', [
                'voucher_code' => $voucher->code ?? 'N/A',
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Parse the feedback array and categorize items into email, SMS, and webhook recipients.
     *
     * @param array $feedback The array of feedback items to process.
     * @return array An array containing categorized feedback recipients.
     */
    protected function getRecipients(array $feedback): array
    {
        $emailRecipients = [];
        $smsRecipients = [];
        $webhookRecipients = [];

        foreach ($feedback as $item) {
            $item = trim($item);

            if ($this->isValidEmail($item)) {
                $emailRecipients[] = $item;
            } elseif ($this->isValidPhilippineMobile($item)) {
                $smsRecipients[] = $item;
            } elseif ($this->isValidUrl($item)) {
                $webhookRecipients[] = $item;
            } else {
                Log::warning("Invalid feedback item ignored: {$item}");
            }
        }

        return [$emailRecipients, $smsRecipients, $webhookRecipients];
    }

    /**
     * Validate if a string is a valid email address.
     *
     * @param string $email The input string to validate.
     * @return bool True if valid email, false otherwise.
     */
    protected function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate if a string is a valid URL.
     *
     * @param string $url The input string to validate.
     * @return bool True if valid URL, false otherwise.
     */
    protected function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate if a string is a valid Philippine mobile number using Laravel's Validator.
     *
     * @param string $mobile The input string to validate.
     * @return bool True if valid Philippine mobile number, false otherwise.
     */
    protected function isValidPhilippineMobile(string $mobile): bool
    {
        $validator = Validator::make(
            ['mobile' => $mobile],
            ['mobile' => ['required', (new Phone())->mobile()->country('PH')]]
        );

        return !$validator->fails();
    }
}
