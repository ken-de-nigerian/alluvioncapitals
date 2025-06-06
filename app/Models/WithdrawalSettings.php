<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WithdrawalSettings extends Model
{
    protected $table = 'withdrawal_settings';
    protected $casts = [
        'account_number' => 'encrypted',
        'is_default' => 'boolean',
        'verified_at' => 'datetime'
    ];

    protected $fillable = [
        'user_id',
        'bank_code',
        'bank_name',
        'account_number',
        'account_hash',
        'account_name',
        'is_default',
        'verified_at'
    ];

    /**
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function ($model) {
            // Generate hash before creating
            $model->account_hash = self::hashAccountNumber($model->account_number);
        });

        static::updating(function ($model) {
            // Update hash if account number changed
            if ($model->isDirty('account_number')) {
                $model->account_hash = self::hashAccountNumber($model->account_number);
            }
        });
    }

    /**
     * @param string $accountNumber
     * @return string
     */
    public static function hashAccountNumber(string $accountNumber): string
    {
        // Standardized cleaning before hashing
        $cleaned = preg_replace('/[^0-9]/', '', $accountNumber);
        return hash('sha256', $cleaned);
    }

    /**
     * @param int $userId
     * @param string $accountNumber
     * @return bool
     */
    public static function existsForUser(int $userId, string $accountNumber): bool
    {
        return self::where('user_id', $userId)
            ->where('account_hash', self::hashAccountNumber($accountNumber))
            ->exists();
    }

    /**
     * @return BelongsTo<User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Withdrawal>
     */
    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }
}
