<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Bavix\Wallet\Traits\HasWalletFloat;
use Bavix\Wallet\Interfaces\WalletFloat;
use Bavix\Wallet\Interfaces\Wallet;

/**
 * Class User.
 *
 * @property int         $id
 * @property string      $name
 * @property string      $email
 *
 * @method int getKey()
 */
class User extends Authenticatable implements Wallet, WalletFloat
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasWalletFloat;

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

    public function assignCash(Cash $cash)
    {
        // Detach any existing user from this cash
        \DB::table('cash_user')->where('cash_id', $cash->id)->delete();

        // Attach the cash to the new user
        return $this->cashes()->attach($cash->id);
    }
}
