<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Closure;

class SMSRouterService
{
    protected array $routes = [];
    protected RegexBuilder $regexBuilder;

    public function __construct()
    {
        $this->regexBuilder = new RegexBuilder();
    }

    public function register(string $pattern, Closure $callback): void
    {
        Log::info("Registering SMS command", ['pattern' => $pattern]);
        $this->routes[$pattern] = $callback;
    }

    public function handle(string $message, string $from, string $to): JsonResponse
    {
        Log::info("Handling incoming SMS", [
            'message' => $message,
            'from' => $from,
            'to' => $to
        ]);

        Log::info("Routes", ['routes' => $this->routes]);

        foreach ($this->routes as $pattern => $callback) {
            $regex = $this->regexBuilder->getRegex($pattern);
            $matches = $this->regexBuilder->getValues($regex, $message);

            Log::debug("Checking pattern", ['pattern' => $pattern, 'regex' => $regex, 'matches' => $matches]);

            if (!empty($matches)) {
                Log::info("Match found", ['pattern' => $pattern, 'matches' => $matches]);

                // Pass `from` and `to` along with the parsed matches
                return call_user_func($callback, $matches, $from, $to);
            }
        }

        Log::warning("Unknown SMS command received", ['message' => $message]);

        return response()->json(['message' => 'Unknown command. Please try again.']);
    }

//    public function handle(string $message): JsonResponse
//    {
//        Log::info("Handling incoming SMS", ['message' => $message]);
//        Log::info("Routes", ['routes' => $this->routes]);
//
//        foreach ($this->routes as $pattern => $callback) {
//            $regex = $this->regexBuilder->getRegex($pattern);
//            $matches = $this->regexBuilder->getValues($regex, $message);
//
//            Log::debug("Checking pattern", ['pattern' => $pattern, 'regex' => $regex, 'matches' => $matches]);
//
//            if (!empty($matches)) {
//                Log::info("Match found", ['pattern' => $pattern, 'matches' => $matches]);
//
//                return call_user_func($callback, $matches);
//            }
//        }
//
//        Log::warning("Unknown SMS command received", ['message' => $message]);
//
//        return response()->json(['message' => 'Unknown command. Please try again.']);
//    }
}
