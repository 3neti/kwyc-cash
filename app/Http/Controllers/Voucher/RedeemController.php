<?php

namespace App\Http\Controllers\Voucher;

use FrittenKeeZ\Vouchers\Models\Voucher;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Data\VoucherData;
use Illuminate\Support\Facades\Session;

class RedeemController extends Controller
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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }


    public function show(Request $request, $voucher)
    {
        Session::forget(['voucher_checked', 'mobile_checked', 'signature_checked', 'voucher_redeemed']);
        $voucher = Voucher::where('code', $voucher)->first();
        $data = VoucherData::fromModel($voucher);
//        dd('redeem.show', $request->all(), session()->getOldInput(), $voucher);

        return inertia('Redeem/Success', [
            'voucher' => $data,
            'redirectTimeout' => config('kwyc-cash.redeem.success.redirect_timeout')
        ]);

//        return redirect()->route('rider');
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
