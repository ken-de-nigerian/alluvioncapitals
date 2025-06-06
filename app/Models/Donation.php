<?php

namespace App\Models;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static select(Expression|\Illuminate\Database\Query\Expression $raw, Expression|\Illuminate\Database\Query\Expression $raw1, Expression|\Illuminate\Database\Query\Expression $raw2, Expression|\Illuminate\Database\Query\Expression $raw3)
 * @method static create(array $array)
 * @method static where(string $string, float|int|string $donationId)
 * @property mixed $amount
 * @property mixed $id
 * @property mixed $email
 * @property mixed $phone_number
 * @property mixed $first_name
 * @property mixed $last_name
 * @property mixed $gateway
 * @property mixed $campaign_id
 * @property mixed $campaign
 */
class Donation extends Model
{
    protected $fillable = [
        'campaign_id',
        'reward_id',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'anonymous',
        'gateway',
        'channel',
        'transaction_reference',
        'amount',
        'status',
        'shipping_country',
        'shipping_state',
        'shipping_city',
        'shipping_postal_code',
        'shipping_address',
        'requires_shipping'
    ];

    /**
     * Get the campaign that owns the donation.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
