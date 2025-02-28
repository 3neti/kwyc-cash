<?php

namespace App\Actions;

use App\Models\Cash;
use FrittenKeeZ\Vouchers\Facades\Vouchers;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use FrittenKeeZ\Vouchers\Models\Voucher;
use App\Models\User;

class GenerateCashVouchers
{
    use AsAction;

    protected function generateCashVouchers(User $user, array $validated)
    {
        $qty = $validated['qty'];
        $value = $validated['value'];
        $collection = new Collection();

        for ($i = 0; $i < $qty; $i++) {
            $cash = Cash::create(['value' => $value]);
            $entities = compact('cash');

            // Assign the cash to the user
            $user->assignCash($cash);

            // Generate a voucher linked to this cash
            $voucher = Vouchers::withEntities(...$entities)->withOwner($user)->create();
            $collection->add($voucher);
        }

        return $collection;
    }

    public function handle(User $user, array $params)
    {
        return $this->generateCashVouchers($user, validator($params, $this->rules())->validate());
    }

    public function rules(): array
    {
        return [
            'value' => ['required', 'numeric', 'min:100'],
            'qty' => ['required', 'int', 'min:1']
        ];
    }
}
