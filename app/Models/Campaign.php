<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property mixed $id
 * @method static whereIn(string $string, mixed $ids)
 * @method static create([]|array|false[]|string[] $array_merge)
 * @method static where(string $string, string $string1)
 * @method static min(string $string)
 * @method static max(string $string)
 * @property mixed $title
 * @property mixed $first_image
 * @property mixed $funds_raised
 * @property mixed $goal
 * @property mixed $progress
 * @property mixed $created_at
 * @property mixed $first_name
 * @property mixed $last_name
 * @property mixed $days_left_text
 * @property mixed $status_badge
 * @property mixed $slug
 * @property mixed $user_id
 */
class Campaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'address',
        'city',
        'state',
        'state_id',
        'country',
        'country_id',
        'title',
        'slug',
        'summary',
        'campaign_images',
        'campaign_video',
        'supporting_documents',
        'goal',
        'funds_raised',
        'featured',
        'has_ad_promotion',
        'status',
        'is_complete',
        'expires_at'
    ];

    protected $appends = [
        'progress',
        'days_left_text',
        'status_badge',
        'first_image',
        'show_route',
    ];

    /**
     * @return BelongsTo<User>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Category>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<Reward>
     */
    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }

    /**
     * @return HasMany<Donation>
     */
    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    /**
     * @return HasMany<Comment>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return HasMany<Update>
     */
    public function updates(): HasMany
    {
        return $this->hasMany(Update::class);
    }

    /**
     * Get the first campaign image URL or default.
     */
    public function getFirstImageAttribute(): string
    {
        $images = json_decode($this->campaign_images ?? '[]', true);

        if (is_array($images) && count($images)) {
            $firstImage = trim($images[0]);
            return $firstImage !== '' ? $firstImage : asset('storage/default-image.jpg');
        }

        return asset('storage/default-image.jpg');
    }

    /**
     * Calculate campaign progress percentage
     */
    public function getProgressAttribute(): float
    {
        if (is_null($this->funds_raised) || is_null($this->goal) || $this->goal <= 0) {
            return 0.0;
        }

        $progress = ($this->funds_raised / $this->goal) * 100;
        return min(round($progress, 2), 100);
    }

    /**
     * Calculate days remaining until campaign expires
     */
    public function getDaysLeftAttribute(): int
    {
        if (empty($this->expires_at)) {
            return 0;
        }

        try {
            $now = now();
            $expiresAt = Carbon::parse($this->expires_at);
            return $now->diffInDays($expiresAt);
        } catch (Exception) {
            return 0;
        }
    }

    /**
     * Get human-readable days left text
     */
    public function getDaysLeftTextAttribute(): string
    {
        $daysLeft = $this->days_left;

        if ($daysLeft < 0) {
            return 'Expired';
        }
        if ($daysLeft === 0) {
            return 'Ends today';
        }
        return $daysLeft . ' ' . str('day')->plural($daysLeft) . ' left';
    }

    /**
     * Get status badge information
     */
    public function getStatusBadgeAttribute(): array
    {
        if ($this->is_complete === 'yes') {
            return ['text' => 'Completed', 'class' => 'bg-success bg-opacity-10 text-success'];
        }

        $daysLeft = $this->days_left;
        $progress = $this->progress;

        if ($daysLeft < 0) {
            return ['text' => 'Expired', 'class' => 'bg-primary bg-opacity-10 text-primary'];
        }
        if ($progress >= 100) {
            return ['text' => 'Funded', 'class' => 'bg-success bg-opacity-10 text-success'];
        }
        if ($progress > 0) {
            return ['text' => 'Running', 'class' => 'bg-warning bg-opacity-10 text-warning'];
        }

        return ['text' => 'Not Started', 'class' => 'bg-secondary bg-opacity-10 text-secondary'];
    }

    /**
     * @return string
     */
    public function getShowRouteAttribute(): string
    {
        return $this->slug
            ? route('campaigns.show', $this->slug)
            : route('campaigns.show', $this->id);
    }

    /**
     * @return array
     */
    public function getDonationAmounts(): array
    {
        $maximum = $this->goal;

        // Ensure the minimum is at least 10% of the maximum or set to 1 if the maximum is too low
        $minimum = $maximum > 10 ? ceil(($maximum * 0.1) / 100) * 100 : 1;

        // Calculate step value based on the range; ensure a fallback step if the range is too small
        $range = $maximum - $minimum;
        $step = $range > 3 ? ceil(($range / 3) / 100) * 100 : 100;

        $amounts = [];
        for ($amount = $minimum; $amount <= $maximum + 1; $amount += $step) {
            $roundedAmount = ceil($amount / 100) * 100;
            if ($roundedAmount > 0 && !in_array($roundedAmount, $amounts)) {
                $amounts[] = $roundedAmount;
            }
        }

        return $amounts;
    }
}
