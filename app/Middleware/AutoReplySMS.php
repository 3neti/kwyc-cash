<?php

namespace App\Middleware;

use App\Handlers\AutoReplies\{HelloAutoReply, HelpAutoReply, LoginAutoReply, PingAutoReply};
use Illuminate\Support\Facades\Notification;
use App\Contracts\AutoReplyInterface;
use Illuminate\Support\Facades\Log;
use App\Notifications\SMSAutoReply;
use Closure;

class AutoReplySMS implements SMSMiddlewareInterface
{
    protected array $handlers = [
        'HELLO' => HelloAutoReply::class,
        'HELP' => HelpAutoReply::class,
        'PING' => PingAutoReply::class,
        'LOGIN' => LoginAutoReply::class
    ];

    public function handle(string $message, string $from, string $to, Closure $next)
    {
        $keyword = strtoupper(strtok($message, " "));

        if (isset($this->handlers[$keyword])) {
            $handlerClass = $this->handlers[$keyword];

            if (class_exists($handlerClass) && is_subclass_of($handlerClass, AutoReplyInterface::class)) {
                $handler = new $handlerClass();
                $reply = $handler->reply($from, $to, $message);

                if (!is_null($reply)) { // ✅ Only send auto-reply if it's not null
                    Log::info("AutoReply Sent", compact('from', 'to', 'reply'));

                    Notification::route('engage_spark', $from)
                        ->notify(new SMSAutoReply($reply));

                    return response()->json(['message' => "AutoReply: " . $reply]); // 🔥 Early Exit
                }
            }
        }

        return $next($message, $from, $to); // Continue to logging & storage
    }
}
