<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;

/**
 *
 *
 * @property int $id
 * @property int $project_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Project $project
 * @property-read Collection<int, Version> $versions
 * @property-read int|null $versions_count
 * @method static Builder<static>|Channel newModelQuery()
 * @method static Builder<static>|Channel newQuery()
 * @method static Builder<static>|Channel query()
 * @mixin Eloquent
 */
class Channel extends Model
{
    protected $fillable = ['project_id', 'name'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function versions(): HasManyThrough
    {
        return $this->hasManyThrough(
            Version::class, VersionChannel::class,
            'channel_id', 'id', 'id', 'version_id'
        );
    }

    public function getRouteKeyName(): string
    {
        return 'name';
    }
}
