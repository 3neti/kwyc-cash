<?php

namespace App\Actions;

use Illuminate\Validation\ValidationException;
use FrittenKeeZ\Vouchers\Facades\Vouchers;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Events\CashVouchersGenerated;
use Illuminate\Support\Collection;
use App\Models\{Cash, User};

/**
 * Generates a collection of cash vouchers for a specified user.
 *
 * This action validates the input parameters, creates cash instances, assigns
 * them to the user, and generates vouchers linked to the cash instances.
 */
class GenerateCashVouchers
{
    use AsAction;

    /**
     * Handle the generation of cash vouchers.
     *
     * @param User $user The user to assign the cash vouchers to.
     * @param array $params The validated input parameters.
     * @return Collection The generated collection of vouchers.
     * @throws ValidationException
     */
    public function handle(User $user, array $params): Collection
    {
        $validated = validator($params, $this->rules())->validate();

        $collection = $this->generateCashVouchers($user, $validated);
        CashVouchersGenerated::dispatch($user, $collection);

        return $collection;
    }

    /**
     * Generate the specified quantity of cash vouchers for the user.
     *
     * @param User $user The user to assign the cash vouchers to.
     * @param array $validated The validated input data (value, qty, tag).
     * @return Collection The generated collection of vouchers.
     */
    protected function generateCashVouchers(User $user, array $validated): Collection
    {
        $collection = new Collection();

        foreach (range(1, $validated['qty']) as $index) {
            $cash = $this->createCash($validated['value'], $validated['tag']);

            // Assign cash to the user; skip if assignment fails
            if (!$user->assignCash($cash)) {
                continue;
            }

            // Create a voucher linked to the cash and the user
            $voucher = $this->createVoucher($cash, $user);
            $collection->add($voucher);
        }

        return $collection;
    }

    /**
     * Validation rules for generating cash vouchers.
     *
     * @return array The validation rules.
     */
    public function rules(): array
    {
        return [
            'value' => ['required', 'numeric', 'min:20'],
            'qty' => ['required', 'int', 'min:1'],
            'tag' => ['nullable', 'string', 'min:1'],
        ];
    }

    /**
     * Creates a cash instance with the specified value and tag.
     *
     * @param float $value The monetary value of the cash.
     * @param string|null $tag An optional tag for categorization.
     * @return Cash The created cash instance.
     */
    protected function createCash(float $value, ?string $tag = null): Cash
    {
        return Cash::create([
            'value' => $value,
            'tag' => $tag,
        ]);
    }

    /**
     * Creates a voucher associated with the specified cash and user.
     *
     * @param Cash $cash The cash entity to associate with the voucher.
     * @param User $user The owner of the voucher.
     * @return mixed The created voucher instance.
     */
    protected function createVoucher(Cash $cash, User $user): mixed
    {
        $entities = compact('cash');
        return Vouchers::withEntities(...$entities)
            ->withOwner($user)
            ->create();
    }
}
