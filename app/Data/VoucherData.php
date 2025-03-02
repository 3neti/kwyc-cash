<?php

namespace App\Data;

use App\Models\Contact;
use FrittenKeeZ\Vouchers\Models\Voucher;
use Spatie\LaravelData\Data;
use App\Models\Cash;

class VoucherData extends Data
{
    /**
     * @param string $code The voucher code.
     * @param float $amount The amount disbursed.
     * @param string|null $mobile The mobile number where the fund was disbursed.
     */
    public function __construct(
        public string $code,
        public float $amount,
        public ?string $mobile = null,
        public bool $disbursed
    ) {}

    /**
     * Creates a VoucherData instance from a Voucher model.
     *
     * @param Voucher $voucher The voucher model.
     * @return VoucherData
     */
    public static function fromModel(Voucher $voucher): VoucherData
    {
        $mobile = $voucher->redeemers->first()?->redeemer->mobile ?? null;
        $cash = $voucher->getEntities(Cash::class)->first();

        return new self(
            code: $voucher->getAttribute('code'),
            amount: $cash?->value->getAmount()->toFloat() ?? 0.0,
            mobile: $mobile,
            disbursed: $cash->disbursed
        );
    }
}
