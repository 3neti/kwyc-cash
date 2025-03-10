<?php

namespace App;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasMobile
{
    const DEFAULT_COUNTRY = 'PH';

    protected function Mobile(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                $country = $attributes['country'] ?? self::DEFAULT_COUNTRY;

                return phone($value, $country)->formatForMobileDialingInCountry($country);
            },
            set: function ($value, $attributes) {
                $country = $attributes['country'] ?? self::DEFAULT_COUNTRY;

                return phone($value, $country)->formatForMobileDialingInCountry($country);
            }
        );
    }
}
