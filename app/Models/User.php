<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Bavix\Wallet\Traits\HasWalletFloat;
use Bavix\Wallet\Interfaces\WalletFloat;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Interfaces\Wallet;
use Illuminate\Support\Facades\DB;
use Bavix\Wallet\Traits\CanPay;


/**
 * Class User.
 *
 * @property int         $id
 * @property string      $name
 * @property string      $email
 *
 * @method int getKey()
 * @method Transaction depositFloat(float|int|string $amount, ?array $meta = null, bool $confirmed = true)
 */
class User extends Authenticatable implements Wallet, WalletFloat, Customer
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasWalletFloat, CanPay;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
}
