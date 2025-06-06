<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property mixed $status
 * @property mixed $user
 * @property mixed $id
 * @property mixed $amount
 */
class Withdrawal extends Model
{
    protected $table = 'withdrawals';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_details' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function withdrawal_settings(): BelongsTo
    {
        return $this->belongsTo(WithdrawalSettings::class);
    }
}
