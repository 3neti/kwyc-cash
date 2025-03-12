<?php

namespace App\Actions;

use FrittenKeeZ\Vouchers\Exceptions\VoucherAlreadyRedeemedException;
use Propaganistas\LaravelPhone\Exceptions\CountryCodeException;
use FrittenKeeZ\Vouchers\Exceptions\VoucherNotFoundException;
use Illuminate\Support\Facades\{Hash, Log, Validator};
use Propaganistas\LaravelPhone\Rules\Phone;
use FrittenKeeZ\Vouchers\Facades\Vouchers;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Exceptions\VoucherSecretMismatch;
use FrittenKeeZ\Vouchers\Models\Voucher;
use App\Models\{Cash, Contact};

class RedeemCashVoucher
{
    use AsAction;

    /**
     * Static property to store the error message across action instances.
     */
    protected static ?string $errorMessage = null;

    /**
     * Handles the redemption of a cash voucher.
     *
     * @param string $voucher_code The voucher code to redeem.
     * @param string $mobile The mobile number associated with the voucher.
     * @param string|null $country The country code (default to the Contact's default country).
     * @param array|null $inputs Additional metadata inputs for the voucher redemption.
     * @param string|null $feedback Comma-delimited feedback channels.
     * @return bool True if the redemption was successful, otherwise false.
     */
    public function handle(
        string $voucher_code,
        string $mobile,
        ?string $country = Contact::DEFAULT_COUNTRY,
        ?array $inputs = null,
        ?string $rider = null,
        ?string $feedback = null
    ): bool {
        Log::info('Starting voucher redemption process', [
            'voucher_code' => $voucher_code,
            'mobile' => $mobile,
            'country' => $country,
            'rider' => $rider,
            'feedback' => $feedback,
        ]);

        try {
            $normalizedMobile = $this->normalizeMobileNumber($mobile, $country);
            $contact = $this->getOrCreateContact($normalizedMobile, $country);
            $feedbackItems = $this->validateFeedback($feedback);

            // 🔴 Do not catch `VoucherSecretMismatch` here!
            $this->checkSecret($voucher_code, $normalizedMobile);

            $result = Vouchers::redeem($voucher_code, $contact, array_merge([
                'mobile' => $normalizedMobile,
                'country' => $country,
                'rider' => $rider,
                'feedback' => $feedbackItems
            ], $inputs ?? []));

            if ($result) {
                self::resetErrorMessage();
                Log::info('Voucher redemption successful', [
                    'voucher_code' => $voucher_code,
                    'contact_id' => $contact->id,
                ]);
            }

            return (bool) $result;

        } catch (VoucherNotFoundException $e) {
            $this->handleException($e, 'The voucher code provided was not found.');

        }  catch (VoucherSecretMismatch $e) {
            // Log and rethrow so middleware can handle it
            \Log::warning("VoucherSecretMismatch for voucher: {$voucher_code}");
            throw $e;

        } catch (VoucherAlreadyRedeemedException $e) {
            $this->handleException($e, 'The voucher has already been redeemed.');

        } catch (\Exception $e) {
            $this->handleException($e, 'An unexpected error occurred while redeeming the voucher.');
        }

        return false;
    }

    /**
     * Handles exceptions by setting an error message and logging the error.
     *
     * @param \Exception $e The exception object.
     * @param string $message The custom error message to set.
     */
    protected function handleException(\Exception $e, string $message): void
    {
        self::setErrorMessage($message);
        Log::error($message, [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }

    /**
     * Sets the static error message.
     *
     * @param string $message The error message to store.
     */
    protected static function setErrorMessage(string $message): void
    {
        self::$errorMessage = $message;
    }

    /**
     * Retrieves the static error message, if any.
     *
     * @return string|null The error message, or null if no error occurred.
     */
    public static function getErrorMessage(): ?string
    {
        return self::$errorMessage;
    }

    /**
     * Resets the error message to null.
     */
    protected static function resetErrorMessage(): void
    {
        self::$errorMessage = null;
    }

    /**
     * Validation rules for this action.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'voucher_code' => ['required', 'string', 'min:4'],
            'mobile' => ['required', (new Phone)->type('mobile')->country('PH')],
            'country' => ['nullable', 'string', 'min:2'],
            'inputs' => ['nullable', 'array'],
            'rider' => ['nullable', 'string'],
            'feedback' => ['nullable', 'string'],
        ];
    }

    /**
     * Normalizes a mobile number to the specified country's format.
     *
     * @param string $mobile The mobile number to normalize.
     * @param string $country The country code (default: PH).
     * @return string The normalized mobile number.
     * @throws CountryCodeException
     */
    protected function normalizeMobileNumber(string $mobile, string $country = 'PH'): string
    {
        return phone($mobile, $country)->formatForMobileDialingInCountry($country);
    }

    /**
     * Retrieves or creates a contact based on the mobile and country.
     *
     * @param string $mobile The mobile number.
     * @param string $country The country code.
     * @return Contact The contact instance.
     */
    protected function getOrCreateContact(string $mobile, string $country): Contact
    {
        return Contact::firstOrCreate(compact('mobile', 'country'));
    }

    /**
     * Validates feedback items as valid email, URL, or Philippine mobile number.
     *
     * @param string|null $feedback Comma-delimited string of feedback items.
     * @return array The array of validated feedback items.
     */
    public function validateFeedback(?string $feedback): array
    {
        if (empty($feedback)) {
            return [];
        }
        $feedbackItems = array_map('trim', explode(',', $feedback));
        $validFeedback = array_filter($feedbackItems, function ($item) {
            return $this->isValidEmail($item) ||
                $this->isValidUrl($item) ||
                $this->isValidPhilippineMobile($item);
        });

        return array_values($validFeedback);
    }

    /**
     * Checks if the input is a valid email address.
     *
     * @param string $email
     * @return bool
     */
    protected function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Checks if the input is a valid URL.
     *
     * @param string $url
     * @return bool
     */
    protected function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validates a Philippine mobile number using Laravel's Validator.
     *
     * @param string $mobile
     * @return bool
     */
    protected function isValidPhilippineMobile(string $mobile): bool
    {
        $validator = Validator::make(
            ['mobile' => $mobile],
            ['mobile' => ['required', (new Phone())->mobile()->country('PH')]]
        );

        return !$validator->fails();
    }

    /**
     * Validates the voucher secret against the normalized mobile number.
     *
     * This method retrieves the associated `Cash` entity of the given voucher code
     * and checks if the hashed `secret` matches the normalized mobile number.
     * If a mismatch occurs, a `VoucherSecretMismatch` exception is thrown.
     *
     * @param string $voucher_code The unique code of the voucher.
     * @param string $normalizedMobile The normalized mobile number to check against the secret.
     *
     * @throws VoucherSecretMismatch If the provided mobile number does not match the stored hash.
     */
//    private function checkSecret(string $voucher_code, string $normalizedMobile): void
//    {
//        // Retrieve the voucher by code
//        $voucher = Voucher::where('code', $voucher_code)->first();
//
//        // Ensure the voucher exists before proceeding
//        if (!$voucher) {
//            throw new VoucherNotFoundException("Voucher not found.");
//        }
//
//        // Retrieve the cash entity associated with the voucher
//        $cash = $voucher->getEntities(Cash::class)->first();
//
//        // Ensure cash exists and has a secret before checking
//        if (!($cash instanceof Cash) || !isset($cash->secret)) {
//            return; // No secret means no verification needed
//        }
//
//        // If the secret exists but does not match the hash, throw an exception
//        if (!Hash::check($normalizedMobile, $cash->secret)) {
//            throw new VoucherSecretMismatch();
//        }
//    }
    private function checkSecret(string $voucher_code, string $normalizedMobile): void
    {
        // Retrieve the cash entity associated with the voucher code
        $cash = Voucher::where('code', $voucher_code)->first()->getEntities(Cash::class)->first();

        // Check if the cash entity exists and contains a secret
        if ($cash instanceof Cash && isset($cash->secret) && !Hash::check($normalizedMobile, $cash->secret)) {
            throw new VoucherSecretMismatch;
        }
    }
}
