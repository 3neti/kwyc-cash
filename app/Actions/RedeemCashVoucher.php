<?php

namespace App\Actions;

use FrittenKeeZ\Vouchers\Exceptions\VoucherAlreadyRedeemedException;
use FrittenKeeZ\Vouchers\Exceptions\VoucherNotFoundException;
use FrittenKeeZ\Vouchers\Facades\Vouchers;
use FrittenKeeZ\Vouchers\Models\Voucher;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\ActionRequest;
use App\Models\Contact;

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
     * @param array|null $metadata Any additional data.
     * @return bool True if the redemption was successful, otherwise false.
     */
    public function handle(string $voucher_code, string $mobile, ?string $country = Contact::DEFAULT_COUNTRY, ?array $inputs = null): bool
    {
        try {
            $normalizedMobile = $this->normalizeMobileNumber($mobile, $country);
            $contact = $this->getOrCreateContact($normalizedMobile, $country);

            $result = Vouchers::redeem($voucher_code, $contact, array_merge([
                'mobile' => $normalizedMobile,
                'country' => $country
            ], $inputs));

            // Reset error message if redemption is successful
            if ($result) {
                self::resetErrorMessage();
            }

            return (bool) $result;

        } catch (VoucherNotFoundException $e) {
            self::setErrorMessage('The voucher code provided was not found.');

        } catch (VoucherAlreadyRedeemedException $e) {
            self::setErrorMessage('The voucher has already been redeemed.');

        } catch (\Exception $e) {
            self::setErrorMessage('An unexpected error occurred while redeeming the voucher.');
        }

        return false;
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

    public function rules(): array
    {
        return [
            'voucher_code' => ['required', 'string', 'min:4'],
            'mobile' => ['required', 'string', 'min:10'],
            'country' => ['nullable', 'string', 'min:2'],
            'inputs' => ['nullable', 'array']
        ];
    }

    protected function normalizeMobileNumber(string $mobile, string $country): string
    {
        return phone($mobile, $country)->formatForMobileDialingInCountry($country);
    }

    protected function getOrCreateContact(string $mobile, string $country): Contact
    {
        return Contact::firstOrCreate(compact('mobile', 'country'));
    }
}
