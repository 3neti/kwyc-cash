<?php

namespace App\Data;

use FrittenKeeZ\Vouchers\Models\Voucher;
use Spatie\LaravelData\Data;
use App\Models\Cash;

class VoucherData extends Data
{
    public function __construct(
        public string $code,
        public float $amount
    ) {}

    public static function fromModel(Voucher $voucher): VoucherData
    {
        $cash = $voucher->getEntities(Cash::class)->first();

        return new self(
            code: $voucher->getAttribute('code'),
            amount: $cash->value->getAmount()->toFloat()
        );
    }
}
