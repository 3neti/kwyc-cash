<?php

namespace App\Http\Controllers;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use App\Events\DepositConfirmedFromUnknownMobile;
use Propaganistas\LaravelPhone\Rules\Phone;
use Illuminate\Support\Facades\Log;
use App\Events\DepositConfirmed;
use App\Actions\DepositAmount;
use Illuminate\Http\Request;
use App\Models\User;

class ConfirmController extends Controller
{
    /**
     * Handle external confirmation of a deposit transaction.
     *
     * This method is invoked by a financial institution to confirm that funds
     * have been transferred using a generated QR code. It validates the input,
     * locates the user by mobile or account number, and triggers a deposit event.
     *
     * @param Request $request The incoming request containing deposit details.
     * @return array The validated request data or an empty array if validation fails.
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request): array
    {
        // Validate the incoming request data with additional security checks
        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:50'],
            'account' => ['nullable', 'numeric', 'starts_with:0', 'max_digits:11'],
            'mobile' => ['required', (new Phone)->type('mobile')->country('PH')],
            'name' => ['nullable', 'string'],
        ]);

        // Attempt to find the user by account number (nominated mobile number),
        // falling back to the mobile number if needed
        $user = User::where('mobile', $validated['account'])
            ->orWhere('mobile', $validated['mobile'])
            ->first();

        if ($user instanceof User) {
            try {
                // Deposit the specified amount into the user's wallet
                $transaction = DepositAmount::run($user, $validated['amount']);

                // Dispatch event with the user and the deposited amount
                DepositConfirmed::dispatch($user, $validated['amount'], $transaction->updated_at);

                // Log the successful transaction
                Log::info('Deposit confirmed for user', [
                    'user_id' => $user->id,
                    'amount' => $validated['amount'],
                    'mobile' => $validated['mobile'],
                ]);

                return [
                    'status' => 'success',
                    'message' => 'Deposit confirmed successfully.',
                    'data' => $validated,
                ];
            } catch (\Exception $e) {
                // Log any exceptions that occur during the deposit process
                Log::error('Failed to process deposit', [
                    'error' => $e->getMessage(),
                    'mobile' => $validated['mobile'],
                    'account' => $validated['account'],
                ]);

                return [
                    'status' => 'error',
                    'message' => 'Failed to process the deposit. Please try again.',
                ];
            }
        }

        // Dispatch event with the user and the deposited amount
        DepositConfirmedFromUnknownMobile::dispatch($validated['mobile'], $validated['amount'], $validated['name']);

        // Log an informational message indicating a new user will be created
        Log::info('No existing user found. Initiating automatic registration and deposit.', [
            'mobile' => $validated['mobile'],
            'account' => $validated['account'],
            'amount' => $validated['amount'],
        ]);

        return [
            'status' => 'pending',
            'message' => 'User not found. Registration and deposit will be processed automatically.',
        ];
    }
}
