<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use FrittenKeeZ\Vouchers\Concerns\HasRedeemers;

/**
 * Class Contact.
 *
 * @property int         $id
 * @property string      $mobile
 * @property string      $country
 *
 * @method int getKey()
 */
class Contact extends Model
{
    /** @use HasFactory<\Database\Factories\ContactFactory> */
    use HasFactory;

    const DEFAULT_COUNTRY = 'PH';

    protected $fillable = [
        'mobile',
        'country'
    ];

    public static function booted(): void
    {
        static::creating(function (Contact $contact) {
            $contact->country = empty($contact->country) ? self::DEFAULT_COUNTRY : $contact->country;
        });
    }

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
