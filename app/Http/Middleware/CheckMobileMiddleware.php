<?php

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Propaganistas\LaravelPhone\Rules\Phone;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Closure;

class CheckMobileMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->get('mobile_checked', false)) {
            return $next($request);
        }
        $mobile = $request->get('mobile');
        if (!$this->isValidPhilippineMobile($mobile))
            throw new \Exception('Wrong number');

//        // Mobile validation logic
//        if (!$request->has('mobile')) {
//            return redirect()->route('home')->withErrors(['error' => 'Mobile number is required.']);
//        }
        session()->put('mobile_checked', true);

        return $next($request);
    }

    /**
     * Validates a Philippine mobile number using Laravel's Validator.
     *
     * @param string $mobile
     * @return bool
     */
    protected function isValidPhilippineMobile(string $mobile): bool
    {
        $validator = Validator::make(
            ['mobile' => $mobile],
            ['mobile' => ['required', (new Phone())->mobile()->country('PH')]]
        );

        return !$validator->fails();
    }
}
