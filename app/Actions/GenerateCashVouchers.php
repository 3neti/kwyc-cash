<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use FrittenKeeZ\Vouchers\Facades\Vouchers;
use Illuminate\Support\Collection;
use App\Models\{Cash, User};

class GenerateCashVouchers
{
    use AsAction;

    protected function generateCashVouchers(User $user, array $validated)
    {
        $qty = $validated['qty'];
        $value = $validated['value'];
        $tag = $validated['tag'];
        $collection = new Collection();

        for ($i = 0; $i < $qty; $i++) {
            $cash = Cash::create(['value' => $value, 'tag' => $tag]);
            $entities = compact('cash');

            // Assign the cash to the user
            if (!$user->assignCash($cash))
                continue;

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
            'qty' => ['required', 'int', 'min:1'],
            'tag' => ['nullable', 'string', 'min:1'],
        ];
    }
}
