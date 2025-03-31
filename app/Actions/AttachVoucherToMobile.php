<?php

namespace App\Actions;

use Illuminate\Validation\ValidationException;
use Propaganistas\LaravelPhone\Rules\Phone;
use Illuminate\Support\Facades\Validator;
use Lorisleiva\Actions\Concerns\AsAction;
use FrittenKeeZ\Vouchers\Models\Voucher;
use App\Models\{Cash, Contact};

class AttachVoucherToMobile
{
    use AsAction;

    /**
     * Main execution logic.
     *
     * @param array $input
     * @return array
     * @throws ValidationException
     */
    public function handle(array $input): array
    {
        Validator::make($input, $this->rules())->validate();

        $mobile = $input['mobile'];
        $voucherCode = $input['voucher_code'];

        $voucher = Voucher::where('code', $voucherCode)->first();

        if (!$voucher) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Invalid voucher code.',
            ]);
        }

        // Attach contact entity to voucher
        $contact = Contact::firstOrCreate(['mobile' => $mobile]);
        $voucher->addEntities($contact);

        // Add mobile as a secret to associated cash entity
        $cash = $voucher->getEntities(Cash::class)->first();
        if ($cash) {
            $cash->secret = $contact->mobile;
            $cash->save();
        }

        // Dispatch share notification
        ShareCashVoucher::dispatch($voucher);

        return [
            'voucher_code' => $voucher->code,
        ];
    }

    public function rules(): array
    {
        return [
            'mobile' => ['required', (new Phone)->type('mobile')->country('PH')],
            'voucher_code' => ['required', 'string'],
        ];
    }
}
