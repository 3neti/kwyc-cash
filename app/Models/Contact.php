<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use App\HasMobile;

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
    use HasMobile;

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
}
