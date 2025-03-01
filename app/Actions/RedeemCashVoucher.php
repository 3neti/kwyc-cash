<?php

namespace App\Actions;

use FrittenKeeZ\Vouchers\Facades\Vouchers;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\ActionRequest;
use App\Models\Contact;

/**
 * Redeems a cash voucher by associating it with a contact.
 *
 * This action can be used as a standalone method, a queued job, or a controller action.
 */
class RedeemCashVoucher
{
    use AsAction;

    /**
     * Handles the redemption of a cash voucher.
     *
     * @param string $voucher_code The voucher code to redeem.
     * @param string $mobile The mobile number associated with the voucher.
     * @param string|null $country The country code (default to the Contact's default country).
     * @return mixed The result of the voucher redemption.
     */
    public function handle(string $voucher_code, string $mobile, ?string $country = Contact::DEFAULT_COUNTRY)
    {
        $normalizedMobile = $this->normalizeMobileNumber($mobile, $country);

        $contact = $this->getOrCreateContact($normalizedMobile, $country);

        return Vouchers::redeem($voucher_code, $contact, [
            'mobile' => $normalizedMobile,
            'country' => $country,
        ]);
    }

    /**
     * Processes the voucher redemption when used as a controller action.
     *
     * @param ActionRequest $request The incoming HTTP request.
     * @return mixed The result of the handle method.
     */
    public function asController(ActionRequest $request)
    {
        return $this->handle(...$request->validated());
    }

    /**
     * Defines the validation rules for the voucher redemption.
     *
     * @return array The validation rules.
     */
    public function rules(): array
    {
        return [
            'voucher_code' => ['required', 'string', 'min:4'],
            'mobile' => ['required', 'string', 'min:10'],
            'country' => ['nullable', 'string', 'min:2'],
        ];
    }

    /**
     * Normalizes the mobile number to the standard format.
     *
     * @param string $mobile The input mobile number.
     * @param string $country The country code for formatting.
     * @return string The normalized mobile number.
     */
    protected function normalizeMobileNumber(string $mobile, string $country): string
    {
        return phone($mobile, $country)->formatForMobileDialingInCountry($country);
    }

    /**
     * Retrieves an existing contact or creates a new one.
     *
     * @param string $mobile The normalized mobile number.
     * @param string $country The country code.
     * @return Contact The contact instance.
     */
    protected function getOrCreateContact(string $mobile, string $country): Contact
    {
        return Contact::firstOrCreate(compact('mobile', 'country'));
    }
}
