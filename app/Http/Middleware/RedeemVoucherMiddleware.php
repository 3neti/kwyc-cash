<?php

namespace App\Http\Middleware;

use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
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
        if (session()->has('voucher_redeemed')) {
            return $next($request);
        }
        // Use the old input to get the payload including the signature data
        $payload = session()->getOldInput();

        $action = app(RedeemCashVoucher::class);
        $validated = Validator::make($payload, $action->rules())->validate();
//        $action->run(...$validated);
        if(!$action->run(...$validated))
            throw new \Exception(RedeemCashVoucher::getErrorMessage());
        session()->put('voucher_redeemed', true);

        return $next($request);
    }
}
