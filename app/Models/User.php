<?php

namespace App\Models;

use Bavix\Wallet\Interfaces\{Customer, Wallet, WalletFloat};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use FrittenKeeZ\Vouchers\Concerns\HasVouchers;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Notifiable;
use Bavix\Wallet\Traits\HasWalletFloat;
use Bavix\Wallet\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Actions\AutoUserCampaign;
use Bavix\Wallet\Traits\CanPay;
use Illuminate\Support\Str;
use App\HasMobile;

/**
 * Class User.
 *
 * @property int         $id
 * @property string      $name
 * @property string      $email
 * @property string      $mobile
 * @property string      $country
 * @property Collection  $campaigns
 * @property Campaign    $currentCampaign
 *
 *
 * @method int getKey()
 * @method Transaction depositFloat(float|int|string $amount, ?array $meta = null, bool $confirmed = true)
 */
class User extends Authenticatable implements Wallet, WalletFloat, Customer
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasWalletFloat, CanPay, HasVouchers, HasMobile;


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile',
        'country'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public static function booted(): void
    {
        static::creating(function (User $user) {
            $user->country = empty($user->country) ? self::DEFAULT_COUNTRY : $user->country;
        });
//        static::created(function (User $user) {
//            AutoUserCampaign::dispatch($user);
//        });
    }

    public function campaigns(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function cashes()
    {
        return $this->belongsToMany(Cash::class, 'cash_user', 'user_id', 'cash_id')
            ->using(CashUser::class)
            ->withTimestamps(); // Ensures pivot timestamps are updated
    }

    public function assignCash(Cash $cash): bool
    {
        $success = false;

        DB::beginTransaction();
        try {
            $this->pay($cash);
            // Detach any existing user from this cash
            DB::table('cash_user')->where('cash_id', $cash->id)->delete();
            // Attach the cash to the new user
            $this->cashes()->attach($cash);
            DB::commit();
            $success = true;
        } catch (\Exception $exception) {
            DB::rollback();
        }

        return $success;
    }

    protected function Name(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                return Str::title($value);
            },
        );
    }

    public function currentCampaign(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    /**
     * Accessor for the currentCampaign attribute.
     */
    public function getCurrentCampaignAttribute()
    {
        return $this->currentCampaign()->first();
    }

    /**
     * Mutator for the currentCampaign attribute.
     *
     * @param Campaign|int $value The ID of the campaign to associate with the user.
     */
    public function setCurrentCampaignAttribute(Campaign|int $value): void
    {
        $this->attributes['campaign_id'] = $value instanceof Campaign ? $value->id : $value;
    }
}
