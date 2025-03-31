<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use DateInterval;

class MacroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Str::macro('normalizeDuration', function (?string $duration): string {
            if (!$duration) return 'PT12H';

            $duration = strtoupper($duration);

            // Already in ISO format?
            if (str_starts_with($duration, 'P')) {
                try {
                    new DateInterval($duration);
                    return $duration;
                } catch (\Exception) {
                    return 'PT12H';
                }
            }

            // Parse shorthand like 1d2h30m10s
            preg_match_all('/(\d+)([DHMS])/', $duration, $parts, PREG_SET_ORDER);

            $date = '';
            $time = '';

            foreach ($parts as [$_, $val, $unit]) {
                if ($unit === 'D') {
                    $date .= "{$val}D";
                } else {
                    $time .= "{$val}{$unit}";
                }
            }

            $normalized = 'P' . $date . ($time ? 'T' . $time : '');

            try {
                new DateInterval($normalized);
                return $normalized;
            } catch (\Exception) {
                return 'PT12H';
            }
        });

        Str::macro('isMobileNumber', function (?string $number, string $country = 'PH'): bool {
            if (!$number) return false;

            // Normalize: remove spaces or dashes, etc.
            $number = preg_replace('/[\s\-]/', '', $number);

            $validator = Validator::make(
                ['number' => $number],
                ['number' => ['phone:' . $country . ',mobile']]
            );

            return !$validator->fails();
        });

        Str::macro('formatMobileNumber', function (?string $number, string $country = 'PH'): ?string {
            if (!$number) return null;

            try {
                return phone($number, $country)->formatForMobileDialingInCountry($country);
            } catch (\Exception) {
                return null;
            }
        });

        Str::macro('maskMobile', function (?string $number, int $visible = 3, string $country = 'PH'): ?string {
            if (!$number) return null;

            try {
                // Format using local mobile-dialing format
                $formatted = phone($number, $country)->formatForMobileDialingInCountry($country);
            } catch (\Exception) {
                return null;
            }

            // Clean formatting (remove spaces/dashes/parentheses)
            $clean = preg_replace('/[^\d]/', '', $formatted);
            $length = strlen($clean);

            if ($length <= $visible * 2) return $clean;

            return substr($clean, 0, $visible) .
                str_repeat('•', $length - ($visible * 2)) .
                substr($clean, -$visible);
        });
    }
}
