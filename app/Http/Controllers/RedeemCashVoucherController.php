<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\{Log, Session};
use Illuminate\Support\Facades\Validator;
use FrittenKeeZ\Vouchers\Models\Voucher;
use App\Actions\RedeemCashVoucher;
use Illuminate\Http\Request;
use App\Data\VoucherData;
use App\Models\Cash;

class RedeemCashVoucherController extends Controller
{
    /**
     * @deprecated
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Session::forget(['voucher_code', 'redeemer_data', 'signature_data']);

        return inertia()->render('Voucher/Redeem', [
            'referenceLabel' => config('kwyc-cash.redeem.reference.label'),
        ]);
    }

    /**
     * @deprecated
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Start timer
        $startTime = microtime(true);
        $action = app(RedeemCashVoucher::class);

        // Validate the incoming request
        $validated = Validator::make($request->all(), $action->rules())->validate();

        // Attempt to redeem the voucher
        $result = $action->run(...$validated);

        if (!$result) {
            $elapsedTime = round(microtime(true) - $startTime, 2);
            Log::info('Voucher redemption failed', ['elapsed_time' => "{$elapsedTime}s"]);

            return back()->with('warning', RedeemCashVoucher::getErrorMessage() ?? 'Voucher redemption failed!')
                ->with('elapsed_time', "{$elapsedTime}s");
        }

        // Retrieve the voucher and convert to data class
        $voucher = Voucher::where('code', $validated['voucher_code'])->firstOrFail();
        $voucherData = VoucherData::fromModel($voucher);

        // Calculate elapsed time
        $elapsedTime = round(microtime(true) - $startTime, 2);
        Log::info('Voucher redeemed successfully', ['elapsed_time' => "{$elapsedTime}s"]);

        return back()->with('data', $voucherData);
    }

    /**
     * TODO: transfer this
     */
    public function show(string $voucher)
    {
        $voucher = Voucher::where('code', $voucher)->first();
        $cash = $voucher?->getEntities(Cash::class)->first();

        return response()->json([
            'status' => $voucher?->isRedeemed() ? ($cash?->disbursed ? 'completed' : 'pending') : 'pending',
            'data' => $voucher ? ['code' => $voucher->code, 'disbursed' => $cash?->disbursed] : null,
        ]);
    }
}
