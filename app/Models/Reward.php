<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static create(array $array)
 * @property mixed amount
 */
class Reward extends Model
{
    use softDeletes;

    protected $fillable = [
        'campaign_id',
        'title',
        'description',
        'amount',
        'requires_shipping',
        'status'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'requires_shipping' => 'boolean',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the campaign that owns the reward.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
