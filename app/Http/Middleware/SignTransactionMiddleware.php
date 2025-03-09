<?php

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Closure;

class SignTransactionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->get('signature_checked', false)) {
            return $next($request);
        }
        if($request->has('inputs.signature')) {
            app('redirect')->setIntendedUrl($request->path());
            session()->put('signature_checked', true); // Set the flag

            return redirect()->route('signature.create')->withInput($request->all());
        }
        session()->put('signature_checked', true); // Set the flag

        return $next($request);
    }
}
