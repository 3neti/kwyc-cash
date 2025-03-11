<?php

namespace App\Http\Controllers;

use Illuminate\Validation\ValidationException;
use Propaganistas\LaravelPhone\Rules\Phone;
use Illuminate\Support\Facades\Validator;
use FrittenKeeZ\Vouchers\Models\Voucher;
use Spatie\LaravelData\DataCollection;
use App\Actions\GenerateCashVouchers;
use App\Models\{Cash, Contact};
use Illuminate\Http\Request;
use App\Data\VoucherData;

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

    public function handleVoucherAction(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'mobile' => ['required', (new Phone)->type('mobile')->country('PH')],
            'voucher_code' => 'required|string',
            'amount' => 'required|numeric',
        ]);

        $mobile = $request->input('mobile');
        $voucherCode = $request->input('voucher_code');
        $amount = $request->input('amount');

        $voucher = Voucher::where('code', $voucherCode)->first();

        if (!$voucher) {
            return redirect()->back()
                ->withErrors(['voucher_code' => 'Invalid voucher code.']);
        }

        $contact = Contact::firstOrCreate(['mobile' => $mobile]);
        $voucher->addEntities($contact);


        return back()->with('event', [
            'name' => 'contact_attached',
            'data' => [
                'voucher_code' => $voucher->code,
            ],
        ]);
    }
}
