<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class SMSData extends Data
{
    public function __construct(
        public string $from,
        public string $to,
        public string $message
    ) {}
}
