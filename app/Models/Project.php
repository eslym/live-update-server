<?php

namespace App\Models;

use Better\Nanoid\Client;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 *
 *
 * @property int $id
 * @property string $nanoid
 * @property string $name
 * @property string|null $description
 * @property string|null $private_key
 * @property string|null $public_key
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Version> $versions
 * @property-read int|null $versions_count
 * @method static Builder<static>|Project newModelQuery()
 * @method static Builder<static>|Project newQuery()
 * @method static Builder<static>|Project query()
 * @mixin Eloquent
 */
class Project extends Model
{
    protected $fillable = ['name', 'description', 'private_key', 'public_key'];

    protected static function boot(): void
    {
        parent::boot();
        self::creating(function ($model) {
            $nanoid = new Client();
            $model->nanoid = $nanoid->produce(21, true);
        });
    }

    public function versions(): HasMany
    {
        return $this->hasMany(Version::class);
    }

    public function getRouteKeyName(): string
    {
        return 'nanoid';
    }
}
