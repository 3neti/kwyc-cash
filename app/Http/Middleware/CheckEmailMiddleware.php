<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckEmailMiddleware
{
    /**
     * The route to redirect to if the email is not valid.
     *
     * @var string
     */
    protected string $register_redirect = 'profile.edit';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {
            $appDomain = parse_url(config('app.url'), PHP_URL_HOST);
            $emailDomain = substr(strrchr($user->email, "@"), 1);

            // Check if the email domain matches the application domain
            if ($emailDomain === $appDomain) {
                // Redirect to the profile edit page if email is invalid
                return redirect()->route($this->register_redirect)
                    ->withErrors(['email' => 'Your email address must not use the domain ' . $appDomain . '.']);
            }
        }

        return $next($request);
    }
}
