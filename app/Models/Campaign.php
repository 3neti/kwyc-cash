<?php

namespace App\Models;

use Spatie\SchemalessAttributes\Casts\SchemalessAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasMetaAttributes;

/**
 * Class Campaign.
 *
 * @property string      $id
 * @property User        $user
 * @property string      $name
 * @property array       $inputs
 * @property string      $feedback
 * @property string      $rider
 * @property string      $url
 * @property bool        $disabled
 * @property string      $reference_label
 *
 * @method int getKey()
 */
class Campaign extends Model
{
    /** @use HasFactory<\Database\Factories\CampaignFactory> */
    use HasMetaAttributes;
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name',
        'inputs',
        'feedback',
        'rider',
        'disabled'
    ];

    protected $appends = [
        'disabled'
    ];

    protected $casts = [
        'inputs' => 'array',
        'meta' => SchemalessAttributes::class,
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function booted(): void
    {
        static::creating(function (Campaign $campaign) {
            $user_id = is_null($campaign->user) ? 'x' : $campaign->user->id;
            $campaign->name = empty($campaign->name) ? $user_id . '-' .substr(strrchr($campaign->id, '-'), 1) : $campaign->name;
        });
    }

    public function scopeWithMeta(): Builder
    {
        return $this->meta->modelScope();
    }

    protected function getUrlAttribute(): string
    {
        $campaign = $this->id;

        return  route('campaign-checkin', compact('campaign'));
    }

    public function getQRCodeURIAttribute(): string
    {
        return generateQRCodeURI(data: $this->url, logo: images_path('id-mark.png'));
    }

    public function setDisabledAttribute(bool $value): self
    {
        $this->setAttribute('disabled_at', $value ? now() : null);

        return $this;
    }

    public function getDisabledAttribute(): bool
    {
        return $this->getAttribute('disabled_at')
            && $this->getAttribute('disabled_at') <= now();
    }
}
