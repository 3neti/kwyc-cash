<?php

namespace App\Http\Controllers;

use App\Actions\GenerateDepositQRCode;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WalletController extends Controller
{
    /**
     * Display the form to load wallet credits.
     */
    public function create()
    {
        return Inertia::render('Auth/LoadWallet', [
            'defaultAmount' => config('kwyc-cash.payment.qr-code.amount'),
            'stepAmount' => config('kwyc-cash.payment.qr-code.increment')
        ]);
    }

    /**
     * Generate a deposit QR code.
     */
    public function generateDepositQRCode(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:50'],
            'account' => ['nullable', 'numeric', 'starts_with:0', 'max_digits:11'],
        ]);

        // Prepare amount and account values
        $amount = $validated['amount'];
        $account = $validated['account'] ?? 'default';

        // Create a unique cache key using amount and account
        $cacheKey = "deposit_qr_{$amount}_{$account}";

        try {
            // Check if the QR code is already cached, or generate and cache it for 30 minutes
            $qrCode = cache()->remember($cacheKey, now()->addMinutes(30), function () use ($amount, $account) {
                logger()->info('Generating new QR code for deposit', compact('amount', 'account'));
                return GenerateDepositQRCode::run($amount, $account);
            });

            return response()->json([
                'success' => true,
                'qr_code' => $qrCode,
            ]);

        } catch (\Exception $e) {
            logger()->error('Failed to generate deposit QR code', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
