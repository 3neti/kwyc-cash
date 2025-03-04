<?php

namespace App\Http\Controllers\API;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use FrittenKeeZ\Vouchers\Models\Voucher;
use App\Http\Controllers\Controller;
use App\Actions\RedeemCashVoucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Data\VoucherData;

class RedeemCashVoucherController extends Controller
{
    /**
     * Redeem a cash voucher and initiate the disbursement process.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function redeem(Request $request): JsonResponse
    {
        $action = app(RedeemCashVoucher::class);

        // Validate the incoming request using the action's rules
        $validated = Validator::make($request->all(), $action->rules())->validate();

        // Attempt to redeem the voucher using the action
        $result = $action->run(...$validated);

        // If redemption failed, return an error response
        if (!$result) {
            return response()->json([
                'status' => 'error',
                'message' => RedeemCashVoucher::getErrorMessage() ?? 'Voucher redemption failed!',
            ], 400);
        }

        // Retrieve the redeemed voucher and prepare the response data
        $voucher = Voucher::where('code', $validated['voucher_code'])->firstOrFail();
        $voucherData = VoucherData::fromModel($voucher);

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher redeemed successfully! Waiting for disbursement.',
            'data' => $voucherData->toArray(),
        ]);
    }

    /**
     * Check the status of the voucher disbursement.
     *
     * @param string $voucherCode
     * @return JsonResponse
     */
    public function status(string $voucherCode): JsonResponse
    {
        // Validate the voucher code input
        $validated = Validator::make(
            ['voucher_code' => $voucherCode],
            ['voucher_code' => ['required', 'string', 'min:4']]
        )->validate();

        $voucher = Voucher::where('code', $validated['voucher_code'])->first();

        if (!$voucher) {
            return response()->json([
                'status' => 'error',
                'message' => 'Voucher not found',
            ], 404);
        }

        $status = $voucher->isRedeemed()
            ? ($voucher->getEntities('App\Models\Cash')->first()?->disbursed ? 'completed' : 'pending')
            : 'pending';

        return response()->json([
            'status' => $status,
            'data' => ['code' => $voucher->code],
        ]);
    }

    /**
     * Display detailed information about a specific voucher.
     *
     * @param string $voucherCode
     * @return JsonResponse
     */
    public function show(string $voucherCode): JsonResponse
    {
        // Validate the voucher code format
        $validated = Validator::make(
            ['voucher_code' => $voucherCode],
            ['voucher_code' => ['required', 'string', 'min:4']]
        )->validate();

        $voucher = Voucher::where('code', $validated['voucher_code'])->first();

        if (!$voucher) {
            return response()->json([
                'status' => 'error',
                'message' => 'Voucher not found',
            ], 404);
        }

        $voucherData = VoucherData::fromModel($voucher);

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher details retrieved successfully.',
            'data' => $voucherData->toArray(),
        ]);
    }
}
