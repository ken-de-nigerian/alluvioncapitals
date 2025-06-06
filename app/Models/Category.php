<?php

namespace App\Models;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property mixed $image
 * @property mixed $id
 * @method static where(string $string, string $string1)
 * @method static create(array $array)
 * @method static withCount(Closure[] $array)
 * @method static whereIn(string $string, mixed $ids)
 * @method static orderBy(string $string)
 * @method static select(string[] $array)
 */
class Category extends Model
{
    use SoftDeletes;

    protected $table = 'categories';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    /**
     * @return HasMany<Campaign>
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }
}
