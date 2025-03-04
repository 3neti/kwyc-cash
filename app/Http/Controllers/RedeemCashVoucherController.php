<?php

namespace App\Http\Controllers;

use App\Models\Cash;
use Illuminate\Support\Facades\Validator;
use FrittenKeeZ\Vouchers\Models\Voucher;
use Illuminate\Support\Facades\Log;
use App\Actions\RedeemCashVoucher;
use Illuminate\Http\Request;
use App\Data\VoucherData;

class RedeemCashVoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia()->render('Voucher/Redeem', [
            'referenceLabel' => config('kwyc-cash.redeem.reference.label'),
            'defaultReference' => config('kwyc-cash.redeem.reference.value'),
            'metaLabel' => config('kwyc-cash.redeem.meta.label')
        ]);
    }

    /**
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
     * Display the specified resource.
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
