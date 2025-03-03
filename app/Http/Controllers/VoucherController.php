<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;
use App\Actions\GenerateCashVouchers;
use FrittenKeeZ\Vouchers\Models\Voucher;
use App\Models\Cash;

class VoucherController extends Controller
{
    /**
     * Show the voucher generation page.
     */
    public function create()
    {
        return inertia('Voucher/Generate');
    }

    /**
     * Generate cash vouchers for the logged-in user.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $user->depositFloat(1000);

        $action = app(GenerateCashVouchers::class);
        $validated = Validator::make($request->all(), $action->rules())->validate();

        $vouchers = GenerateCashVouchers::run($user, $validated);

        $voucherData = $vouchers->map(function (Voucher $voucher) {
            return [
                'code' => $voucher->code,
                'value' => $voucher->getEntities(Cash::class)->first()?->value->getAmount()->toFloat(),
                'tag' => $voucher->getEntities(Cash::class)->first()?->tag,
            ];
        })->toArray();

        return redirect()->back()
            ->with('message', 'Vouchers generated successfully!')
            ->with('data', $voucherData);
    }
}
