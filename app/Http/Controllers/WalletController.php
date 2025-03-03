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
            'balance' => $user->balanceFloat,
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
}
