<?php

namespace App\Pipes;

class AppendSignature
{
    public function handle(string $message, \Closure $next)
    {
        $signature = "\nby App Name";

        return $next($message . $signature);
    }
}
