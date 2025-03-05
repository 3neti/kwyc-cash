<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Models\Cash;

/**
 * CashData is a data transfer object (DTO) representing detailed information about cash.
 *
 * This class encapsulates the financial value, status, and metadata of a cash entity,
 * and is primarily used in conjunction with VoucherData.
 */
class CashData extends Data
{
    /**
     * @param float $value The monetary value of the cash.
     * @param string $currency The currency code (e.g., 'PHP').
     * @param string|null $tag An optional tag for categorizing the cash.
     * @param string $status The current status of the cash (e.g., 'minted', 'suspended').
     * @param bool $suspended Indicates if the cash is currently suspended.
     * @param bool $nullified Indicates if the cash has been nullified.
     * @param bool $expired Indicates if the cash has expired.
     */
    public function __construct(
        public float $value,
        public string $currency,
        public ?string $tag,
        public string $status,
        public bool $suspended,
        public bool $nullified,
        public bool $expired
    ) {}

    /**
     * Creates a CashData instance from a Cash model.
     *
     * @param Cash $cash The cash model instance.
     * @return CashData A populated data object with detailed cash properties.
     */
    public static function fromModel(Cash $cash): CashData
    {
        return new self(
            value: $cash->value->getAmount()->toFloat(),
            currency: $cash->currency,
            tag: $cash->tag,
            status: $cash->status,
            suspended: $cash->suspended,
            nullified: $cash->nullified,
            expired: $cash->expired
        );
    }
}
