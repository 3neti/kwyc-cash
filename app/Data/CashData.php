<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Models\Cash;

class CashData extends Data
{
    public function __construct(
        public float $value,
        public string $currency,
        public ?string $tag,
        public string $status,
        public bool $suspended,
        public bool $nullified,
        public bool $expired
    ) {}

    public static function fromModel(Cash $cash): CashData
    {
        return new self(
            value:$cash->value->getAmount()->toFloat(),
            currency: $cash->currency,
            tag: $cash->tag,
            status: $cash->status,
            suspended: $cash->suspended,
            nullified: $cash->nullified,
            expired: $cash->expired
        );
    }
}
