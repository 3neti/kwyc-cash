<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use App\Services\QuoteService;

/**
 * Class Quote.
 *
 * @method static string get()
 */
class Quote extends Facade
{
    protected static function getFacadeAccessor()
    {
        return QuoteService::class;
    }
}
