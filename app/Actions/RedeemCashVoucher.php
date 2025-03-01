<?php

namespace App\Actions;

use FrittenKeeZ\Vouchers\Facades\Vouchers;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Contact;

class RedeemCashVoucher
{
    use AsAction;

    public function handle(string $voucher_code, string $mobile, ?string $country = Contact::DEFAULT_COUNTRY)
    {
        $mobile = phone($mobile, $country)->formatForMobileDialingInCountry($country);
        $contact = Contact::firstOrCreate(compact('mobile', 'country'));

        return Vouchers::redeem($voucher_code, $contact);
    }

    public function rules(): array
    {
        return [
            'voucher_code' => ['required', 'string', 'min:4'],
            'mobile' => ['required', 'string', 'min:10'],
            'country' => ['nullable', 'string', 'min:2'],
        ];
    }
}
