<?php

namespace App\Models;

use App\Models\Concerns\HasNanoId;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 *
 *
 * @property int $id
 * @property string $nanoid
 * @property int $project_id
 * @property string $name
 * @property string $path
 * @property string|null $signature
 * @property string|null $android_requirements
 * @property string|null $ios_requirements
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Project $project
 * @property-read Collection<int, VersionResolution> $resolutions
 * @property-read int|null $resolutions_count
 * @method static Builder<static>|Version newModelQuery()
 * @method static Builder<static>|Version newQuery()
 * @method static Builder<static>|Version query()
 * @mixin Eloquent
 */
class Version extends Model
{
    use HasNanoId;

    protected $fillable = ['name', 'project_id', 'path', 'signature', 'android_requirements', 'ios_requirements'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function resolutions(): HasMany
    {
        return $this->hasMany(VersionResolution::class);
    }
}
