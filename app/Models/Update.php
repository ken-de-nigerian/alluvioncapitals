<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property mixed $campaign_id
 * @property mixed $images
 */
class Update extends Model
{
    protected $fillable = [
        'campaign_id',
        'title',
        'message',
        'images',
        'status'
    ];

    /**
     * Get the campaign that owns the update.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
