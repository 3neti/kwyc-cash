<?php

namespace App\Http\Controllers;

use App\Actions\RedeemCashVoucher;
use FrittenKeeZ\Vouchers\Models\Voucher;
use Illuminate\Http\Request;
use App\Data\VoucherData;
use Illuminate\Support\Facades\Validator;

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
        return inertia()->render('Voucher/Redeem');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $action = app(RedeemCashVoucher::class);

        // Validate the incoming request
        $validated = Validator::make($request->all(), $action->rules())->validate();

        // Attempt to redeem the voucher
        $result = $action->run(...$validated);

        if (!$result) {
            return back()->with('warning', 'Voucher redemption failed!');
        }

        // Retrieve the voucher and convert to data class
        $voucher = Voucher::where('code', $validated['voucher_code'])->firstOrFail();

        $voucherData = VoucherData::fromModel($voucher);

        return back()
            ->with('message', 'Voucher redeemed successfully!')
            ->with('data', $voucherData);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
