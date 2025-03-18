<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Data\SMSData;

/**
 * Class SMS.
 *
 * @property int         $id
 * @property string      $from
 * @property string      $to
 * @property string      $message
 *
 *
 * @method int getKey()
 */
class SMS extends Model
{
    /** @use HasFactory<\Database\Factories\SMSFactory> */
    use HasFactory;

    protected $fillable = [
        'from',
        'to',
        'message'
    ];

    public static function createFromSMSData(SMSData $SMSData): static
    {
        return static::create($SMSData->toArray());
    }

    public static function booted(): void
    {
        static::created(function (SMS $sms) {

        });
    }
}
