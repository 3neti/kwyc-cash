<?php

namespace App\Http\Controllers;

use FrittenKeeZ\Vouchers\Models\Voucher;
use Illuminate\Support\Facades\Log;
use App\Actions\RetryDisbursement;
use Illuminate\Http\Request;

class ReDisburseCashVoucherController extends Controller
{
    /**
     * Handle re-disbursement of a cash voucher.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'voucher_code' => 'required|string|exists:vouchers,code',
        ]);

        $voucher = Voucher::where('code', $request->input('voucher_code'))->firstOrFail();

        Log::info("Processing re-disbursement for voucher: {$voucher->code}");

        if (RetryDisbursement::run($voucher)) {
            Log::info("Re-disbursement successful for voucher: {$voucher->code}");
            return redirect()->back()->with('message', 'Cash re-disbursed successfully.');
        }

        Log::warning("Failed to re-disburse voucher: {$voucher->code}");
        return redirect()->back()->withErrors(['message' => 'Failed to re-disburse the voucher.']);
    }
}
