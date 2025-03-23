<?php

namespace App\Actions;

use Illuminate\Validation\ValidationException;
use Propaganistas\LaravelPhone\Rules\Phone;
use FrittenKeeZ\Vouchers\Facades\Vouchers;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Events\CashVouchersGenerated;
use Illuminate\Support\Collection;
use App\Models\{Cash, User};
use Illuminate\Support\Arr;
use DateInterval;

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
     * @throws \Exception
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
     * This method creates multiple cash vouchers for a user, each linked to a cash entity.
     * It also allows setting a custom expiration duration for the vouchers.
     * If no duration is provided, it falls back to the configured default or 12 hours.
     *
     * 🔹 **Use Cases:**
     * - Generating multiple cash vouchers with a fixed or dynamic expiration time.
     * - Ensuring vouchers expire based on a user-defined duration.
     * - Handling bulk voucher generation while maintaining cash assignment logic.
     *
     * @param User  $user      The user to assign the cash vouchers to.
     * @param array $validated The validated input data (`value`, `qty`, `tag`, `duration`).
     *
     * @return Collection The generated collection of vouchers.
     *
     * @throws \Exception If any voucher creation fails.
     *
     * 🔹 **Example Usage:**
     * ```php
     * $vouchers = $this->generateCashVouchers($user, [
     *     'value' => 500,
     *     'qty' => 3,
     *     'duration' => 'P1D', // Expires in 1 day
     *     'tag' => 'bonus',
     * ]);
     * ```
     *
     * 🔹 **How Expiration Works:**
     * - If `duration` is **provided**, it must be a valid ISO 8601 duration (e.g., `PT6H` for 6 hours).
     * - If `duration` is **not provided**, it falls back to `config('kwyc-cash.voucher.duration')`.
     * - If the config value is missing or invalid, the system **defaults to 12 hours (`PT12H`)**.
     */
    protected function generateCashVouchers(User $user, array $validated): Collection
    {
        $collection = new Collection();

        foreach (range(1, $validated['qty']) as $index) {
            // Create a new cash entity with the specified value and tag
            $cash = $this->createCash($validated['value'], $validated['tag']);

            // Assign cash to the user; if assignment fails, skip to the next iteration
            if (!$user->assignCash($cash)) {
                continue;
            }

            // Retrieve the optional duration from validated input
            $duration = Arr::get($validated, 'duration');

            $metadata = Arr::only($validated, ['feedback', 'dedication']);

            // Create a voucher linked to the cash and the user
            $voucher = $this->createVoucher($cash, $user, $duration, $metadata);
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
            'duration' => ['nullable', 'string'],
            'dedication' => ['nullable', 'string'],
            'feedback' => ['required', (new Phone)->type('mobile')->country('PH')],
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
     * This method assigns an expiration time to the voucher, optionally overridden by a custom duration.
     * If no valid custom duration is provided, it falls back to:
     * - The configured default (`config('kwyc-cash.voucher.duration')`).
     * - `PT12H` (12 hours) if the config value is also invalid.
     *
     * 🔹 **Use Cases:**
     * - Creating a cash voucher that expires after a set duration.
     * - Allowing dynamic expiration overrides while ensuring a safe fallback.
     * - Handling invalid durations gracefully instead of throwing exceptions.
     *
     * @param Cash        $cash     The cash entity to associate with the voucher.
     * @param User        $user     The owner of the voucher.
     * @param string|null $duration An optional duration in **ISO 8601** format (e.g., `'PT12H'` for 12 hours).
     *
     * @return mixed The created voucher instance.
     *
     * 🔹 **Example Usage:**
     * ```php
     * $voucher = $this->createVoucher($cash, $user); // Uses default or 12-hour fallback
     * $voucher = $this->createVoucher($cash, $user, 'PT6H'); // Expires in 6 hours
     * $voucher = $this->createVoucher($cash, $user, 'INVALID'); // Expires in 12 hours (fallback)
     * ```
     */
    protected function createVoucher(Cash $cash, User $user, ?string $duration = null, array $metadata = []): mixed
    {
        $entities = compact('cash');

        // Use provided duration or fallback to config
        $duration = $duration ?? config('kwyc-cash.voucher.duration', 'PT12H');

        // Attempt to create DateInterval, fallback to 12 hours if invalid
        try {
            $interval = new DateInterval($duration);
        } catch (\Exception $e) {
            $interval = new DateInterval('PT12H'); // Default fallback
        }

        return Vouchers::withMetadata($metadata)
            ->withEntities(...$entities)
            ->withExpireTimeIn($interval)
            ->withOwner($user)
            ->create();
    }
}
