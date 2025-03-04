<?php

namespace App\Http\Controllers;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use FrittenKeeZ\Vouchers\Models\Voucher;
use Spatie\LaravelData\DataCollection;
use App\Actions\GenerateCashVouchers;
use Illuminate\Http\Request;
use App\Data\VoucherData;
use App\Models\Cash;

class VoucherController extends Controller
{
    public function index(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        $user = $request->user();

        return inertia('Voucher/Index',[
            'vouchers' => new DataCollection(VoucherData::class, $user->vouchers),
        ]);
    }

    /**
     * Show the voucher generation page.
     */
    public function create(): \Inertia\Response|\Inertia\ResponseFactory
    {
        return inertia('Voucher/Generate',[
            'defaultVoucherValue' => config('kwyc-cash.voucher.value'),
            'minAmount' => config('kwyc-cash.voucher.minimum'),
            'stepAmount' => config('kwyc-cash.voucher.increment'),
            'tariffAmount' => config('kwyc-cash.voucher.tariff')
        ]);
    }

    /**
     * Generate cash vouchers for the logged-in user.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws ValidationException
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $action = app(GenerateCashVouchers::class);
        $validated = Validator::make($request->all(), $action->rules())->validate();

        $vouchers = GenerateCashVouchers::run($request->user(), $validated);

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
