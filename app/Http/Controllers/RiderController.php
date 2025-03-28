<?php

namespace App\Http\Controllers;

use FrittenKeeZ\Vouchers\Models\Voucher;
use Illuminate\Http\Request;
use App\Data\VoucherData;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Support\Arr;

class RiderController extends Controller
{
    public function __invoke(Request $request, string $voucher)
    {
        \Log::info("RiderController invoked with voucher: " . $voucher);

        $voucher = Voucher::where('code', $voucher)->first();
        if (!$voucher) {
            \Log::error("Voucher not found: " . $voucher);
            abort(404, "Voucher not found");
        }

        $rider = Arr::get($voucher->redeemers()->first()?->metadata, 'rider');
        \Log::info("Rider URL: " . ($rider ?? 'No Rider Found'));

        return $rider
            ? inertia()->location($rider)
            : redirect(route('home'));
    }

//    public function __invoke(Request $request, string $voucher)
//    {
//        $voucher = Voucher::where('code', $voucher)->first();
//        $rider = Arr::get($voucher->redeemers()->first()?->metadata, 'rider');
//
//        return $rider
//            ? inertia()->location($rider)
//            : redirect(route('home'));
//    }
}
