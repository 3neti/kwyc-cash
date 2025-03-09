<?php

namespace App\Http\Middleware;

use FrittenKeeZ\Vouchers\Exceptions\VoucherAlreadyRedeemedException;
use Symfony\Component\HttpFoundation\Response;
use FrittenKeeZ\Vouchers\Facades\Vouchers;
use Illuminate\Http\Request;
use Closure;

class CheckVoucherMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->get('voucher_checked', false)) {
            return $next($request);
        }
        $voucher_code = $request->route(param: 'voucher');
        if (!Vouchers::redeemable($voucher_code))//TODO: improve this
            throw new VoucherAlreadyRedeemedException;

//        // Your voucher validation logic here
//        // For example:
//        if (!$request->has('voucher')) {
//            return redirect()->route('home')->withErrors(['error' => 'Invalid voucher.']);

        session()->put('voucher_checked', true);

        return $next($request);
    }
}
