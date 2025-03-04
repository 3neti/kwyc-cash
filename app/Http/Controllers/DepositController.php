<?php

namespace App\Http\Controllers;

use App\Actions\GenerateDepositQRCode;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'amount' => ['nullable', 'integer', 'min:50'],
            'account' => ['nullable', 'numeric', 'starts_with:0', 'max_digits:11'],
        ]);

        return GenerateDepositQRCode::run(
            $validated['amount'] ?? null,
            $validated['account'] ?? null
        );
    }
}
