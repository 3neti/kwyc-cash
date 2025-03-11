<?php

namespace App\Actions;

use Illuminate\Support\Facades\Notification;
use Lorisleiva\Actions\Concerns\AsAction;
use FrittenKeeZ\Vouchers\Models\Voucher;
use App\Notifications\ContactVoucher;
use App\Data\VoucherData;

class ShareCashVoucher
{
    use AsAction;

    public function handle(Voucher $voucher): void
    {
        $data = VoucherData::fromModel($voucher);
        $mobile = $data->contact->mobile;
        Notification::route('engage_spark', $mobile)
            ->notify(new ContactVoucher(VoucherData::fromModel($voucher)));
    }
}
