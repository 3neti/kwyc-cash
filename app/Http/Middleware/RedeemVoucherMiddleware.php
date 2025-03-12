<?php

namespace App\Http\Middleware;

use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\VoucherSecretMismatch;
use Illuminate\Support\Facades\Validator;
use App\Actions\RedeemCashVoucher;
use Illuminate\Http\Request;
use Closure;

class RedeemVoucherMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     * @throws ValidationException
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->get('voucher_redeemed', false)) {
            return $next($request);
        }
        // Use the old input to get the payload including the signature data
        $payload = session()->getOldInput();

        $action = app(RedeemCashVoucher::class);
        $validated = Validator::make($payload, $action->rules())->validate();
//        $action->run(...$validated);
        try {
            if (!$action->run(...$validated)) {
                throw new \RuntimeException(RedeemCashVoucher::getErrorMessage()); // More specific exception
            }
        } catch (VoucherSecretMismatch $voucherSecretMismatch) {
            \Log::error('VoucherSecretMismatch caught for voucher: ' . $validated['voucher_code']);
            return response()->redirectToRoute('redeem-unassigned', ['voucher' => $validated['voucher_code']]);
//            return redirect()->route('redeem-unassigned', ['voucher' => $validated['voucher_code']]);
        }
        catch (\Exception $exception) {
            \Log::error('Error in Middleware: ' . $exception->getMessage()); // Log error
            return redirect()->back()->withErrors(['message' => 'An unexpected error occurred. Please try again.']);
        }

        session()->put('voucher_redeemed', true);

        return $next($request);
    }
}
