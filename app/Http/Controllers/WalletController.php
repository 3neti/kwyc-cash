<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WalletController extends Controller
{
    /**
     * Display the form to load wallet credits.
     */
    public function create()
    {
        $user = Auth::user();

        return Inertia::render('Auth/LoadWallet', [
            'balance' => (float) $user->balanceFloat,
        ]);
    }

    /**
     * Handle the deposit of wallet credits.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $amount = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ])['amount'];

        // Perform the deposit
        $user->depositFloat($amount);

        return back()->with('event', [
            'name' => 'walletUpdated',
            'data' => [
                'balance' => $user->balanceFloat,
                'message' => 'Wallet updated successfully!',
            ],
        ]);
    }

    public function generateDepositQRCode(Request $request)
    {
        try {
            $qrCode = GenerateDepositQRCode::run(
                $validated['amount'] ?? null,
                $validated['account'] ?? null
            );

            return response()->json([
                'success' => true,
                'qr_code' => $qrCode,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
