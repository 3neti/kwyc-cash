<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class QuoteService
{
    /**
     * Get a random inspirational quote from a public API.
     *
     * @return string
     */
    public function get(): string
    {
        try {
            $response = Http::timeout(5)->get('https://zenquotes.io/api/random');

            $data = $response->json()[0] ?? null;

            if ($data && isset($data['q'], $data['a'])) {
                return "{$data['q']} — {$data['a']}";
            }

            return 'Stay inspired!';
        } catch (\Throwable $e) {
            return 'Keep moving forward. — Unknown';
        }
    }
}
