<?php

namespace App\Data;

use App\Models\Cash;
use Spatie\LaravelData\Data;
use FrittenKeeZ\Vouchers\Models\Voucher;

/**
 * VoucherData is a data transfer object (DTO) representing the details of a voucher.
 *
 * It provides a structured format for passing voucher information, including
 * associated cash data and the redemption status.
 */
class VoucherData extends Data
{
    /**
     * @param string $code The unique voucher code.
     * @param bool $redeemed Indicates if the voucher has been redeemed.
     * @param bool $expired Indicates if the voucher has expired.
     * @param string $created_at The date and time when the voucher was created.
     * @param string $starts_at The date and time when the voucher becomes valid.
     * @param string $expires_at The date and time when the voucher expires.
     * @param CashData $cash The associated cash data (preferred over deprecated $amount).
     * @param float $amount Deprecated. The amount disbursed. Use $cash->value instead.
     * @param bool $disbursed Indicates if the associated cash has been disbursed.
     * @param string|null $mobile The mobile number associated with the voucher, if any.
     */
    public function __construct(
        public string $code,
        public bool $redeemed,
        public bool $expired,
        public string $created_at,
        public string $starts_at,
        public string $expires_at,
        public CashData $cash,
        /** @deprecated Use $cash->value instead */
        public float $amount,
        public bool $disbursed,
        public ?string $mobile = null,
    ) {}

    /**
     * Creates a VoucherData instance from a Voucher model.
     *
     * @param Voucher $voucher The voucher model instance.
     * @return VoucherData A populated data object with voucher and cash details.
     */
    public static function fromModel(Voucher $voucher): VoucherData
    {
        $mobile = $voucher->redeemers->first()?->redeemer->mobile ?? null;
        $cash = $voucher->getEntities(Cash::class)->first();

        return new self(
            code: $voucher->getAttribute('code'),
            redeemed: $voucher->isRedeemed(),
            expired: $voucher->isExpired(),
            created_at: $voucher->created_at->format('Y-m-d H:i:s'),
            starts_at: $voucher->starts_at?->format('Y-m-d H:i:s') ?? '',
            expires_at: $voucher->expires_at?->format('Y-m-d H:i:s') ?? '',
            cash: CashData::fromModel($cash),
            /** @deprecated */
            amount: $cash?->value->getAmount()->toFloat() ?? 0.0,
            disbursed: $cash->disbursed,
            mobile: $mobile
        );
    }
}
