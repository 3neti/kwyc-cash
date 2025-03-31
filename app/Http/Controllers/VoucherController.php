<?php

namespace App\Http\Controllers;

use App\Actions\{AttachVoucherToMobile, GenerateCashVouchers};
use Illuminate\Validation\ValidationException;
use Propaganistas\LaravelPhone\Rules\Phone;
use Illuminate\Support\Facades\Validator;
use FrittenKeeZ\Vouchers\Models\Voucher;
use Spatie\LaravelData\DataCollection;
use App\Actions\ShareCashVoucher;
use App\Models\{Cash, Contact};
use Illuminate\Http\Request;
use App\Data\VoucherData;

class VoucherController extends Controller
{
    public function index(Request $request): \Inertia\Response|\Inertia\ResponseFactory
    {
        $user = $request->user();
        $pages = config('kwyc-cash.ui.vouchers.pages');
        $vouchers = $user->vouchers()
            ->latest() // Orders by latest first
            ->paginate($pages) // Keep pagination enabled
            ->withQueryString(); // Keeps query parameters during navigation

        return inertia('Voucher/Index', [
            'vouchers' => new DataCollection(VoucherData::class, $vouchers->items()),
            'pagination' => [
                'current_page' => $vouchers->currentPage(),
                'last_page' => $vouchers->lastPage(),
                'per_page' => $vouchers->perPage(),
                'total' => $vouchers->total(),
                'next_page_url' => $vouchers->nextPageUrl(),
                'prev_page_url' => $vouchers->previousPageUrl(),
            ],
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
                'expires_at' => $voucher->expires_at,
                'tag' => $voucher->getEntities(Cash::class)->first()?->tag,
            ];
        })->toArray();

        return redirect()->back()
            ->with('message', 'Vouchers generated successfully!')
            ->with('data', $voucherData);
    }

    public function handleVoucherAction(Request $request): \Illuminate\Http\RedirectResponse
    {
        $input = $request->only(['mobile', 'voucher_code']);

        try {
            $result = AttachVoucherToMobile::run($input);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }

        return back()->with('event', [
            'name' => 'contact_attached',
            'data' => $result,
        ]);
    }
}
