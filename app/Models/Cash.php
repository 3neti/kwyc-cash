<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Bavix\Wallet\Interfaces\ProductLimitedInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Bavix\Wallet\Traits\HasWalletFloat;
use Illuminate\Database\Eloquent\Model;
use Bavix\Wallet\Interfaces\Customer;
use Illuminate\Support\Facades\Hash;
use Spatie\ModelStatus\HasStatuses;
use Spatie\LaravelData\WithData;
use Brick\Money\Money;
use App\Data\CashData;

/**
 * Class Cash.
 *
 * @property int         $id
 * @property Money       $value
 * @property string      $currency
 * @property string      $tag
 * @property string      $secret
 * @property string      $status
 * @property bool        $suspended
 * @property bool        $nullified
 * @property bool        $expired
 *
 * @method int getKey()
 * @method Cash setStatus(string $name, ?string $reason = null)
 * @method CashData getData()
 */
class Cash extends Model implements ProductLimitedInterface
{
    /** @use HasFactory<\Database\Factories\CashFactory> */
    use HasWalletFloat;
    use HasStatuses;
    use HasFactory;
    use WithData;

    const INITIAL_STATE = 'minted';
    const SUSPENDED_STATE = 'suspended';
    const NULLIFIED_STATE = 'nullified';

    protected string $dataClass = CashData::class;

    protected $fillable = [
        'value',
        'currency',
        'tag',
        'secret'
    ];

    public static function booted(): void
    {
        static::creating(function (Cash $cash) {
            $cash->currency = empty($cash->currency) ? 'PHP' : $cash->currency;
//            $cash->secret = empty($cash->secret) ? '' : Hash::make($cash->secret);
        });
        static::created(function (Cash $cash) {
            $cash->setStatus(static::INITIAL_STATE);
        });
    }

    protected function Value(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => Money::ofMinor($value, $attributes['currency'] ?? 'PHP'),
            set: fn ($value) =>
            $value instanceof Money
                ? $value->getMinorAmount()->toInt()  // Correctly extract the integer value
                : Money::of($value, 'PHP')->getMinorAmount()->toInt() // Ensure proper conversion
        );
    }

    protected function Secret(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => Hash::make($value)
        );
    }

    public function getAmountProduct(Customer $customer): int|string
    {
        return $this->getAttribute('value') * 100;
    }

    public function getMetaProduct(): ?array
    {
        return [
            'title' => 'cash',
            'description' => 'Cash'
        ];
    }

    public function canBuy(Customer $customer, int $quantity = 1, bool $force = false): bool
    {
        return true;
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'cash_user', 'cash_id', 'user_id')
            ->using(CashUser::class)
            ->withTimestamps()
            ->limit(1); // Ensures that only one user is retrieved per cash
    }

    public function getUserAttribute()
    {
        return $this->users()->first();
    }

    public function setSuspendedAttribute(bool $value): self
    {
        $this->setAttribute('suspended_at', $value ? now() : null);
        $this->setStatus(static::SUSPENDED_STATE);

        return $this;
    }

    public function getSuspendedAttribute(): bool
    {
        return $this->getAttribute('suspended_at')
            && $this->getAttribute('suspended_at') <= now();
    }

    public function setNullifiedAttribute(bool $value): self
    {
        $this->setAttribute('nullified_at', $value ? now() : null);
        $this->setStatus(static::NULLIFIED_STATE);

        return $this;
    }

    public function getNullifiedAttribute(): bool
    {
        return $this->getAttribute('nullified_at')
            && $this->getAttribute('nullified_at') <= now();
    }

    public function getExpiredAttribute(): bool
    {
        return $this->getAttribute('expires_on')
            && $this->getAttribute('expires_on') <= now();
    }
}
