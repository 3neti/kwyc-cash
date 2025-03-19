<?php

namespace App\Middleware;

use App\Handlers\AutoReplies\{HelloAutoReply, HelpAutoReply, PingAutoReply};
use App\Contracts\AutoReplyInterface;
use Illuminate\Support\Facades\Log;
use Closure;

class AutoReplySMS implements SMSMiddlewareInterface
{
    protected array $handlers = [
        'HELLO' => HelloAutoReply::class,
        'HELP' => HelpAutoReply::class,
        'PING' => PingAutoReply::class,
    ];

    public function handle(string $message, string $from, string $to, Closure $next)
    {
        $keyword = strtoupper(strtok($message, " "));

        if (isset($this->handlers[$keyword])) {
            $handlerClass = $this->handlers[$keyword];

            if (class_exists($handlerClass) && is_subclass_of($handlerClass, AutoReplyInterface::class)) {
                $handler = new $handlerClass();
                $reply = $handler->reply($from, $to, $message);

                Log::info("AutoReply Sent", compact('from', 'to', 'reply'));

                // Simulate sending an SMS reply
                // SMSService::send($from, $reply);

                return response()->json(['message' => "AutoReply: " . $reply]); // 🔥 Early Exit
            }
        }

        return $next($message, $from, $to); // Continue to logging & storage
    }
}
