<?php

namespace App\Models;

use App\Models\Concerns\HasNanoId;
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
 * @property string $private_key
 * @property string $public_key
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, \App\Models\VersionResolution> $version_resolutions
 * @property-read int|null $version_resolutions_count
 * @property-read Collection<int, \App\Models\Version> $versions
 * @property-read int|null $versions_count
 * @method static Builder<static>|Project newModelQuery()
 * @method static Builder<static>|Project newQuery()
 * @method static Builder<static>|Project query()
 * @mixin Eloquent
 */
class Project extends Model
{
    use HasNanoId;

    protected $fillable = ['name', 'description', 'private_key', 'public_key'];

    public function versions(): HasMany
    {
        return $this->hasMany(Version::class);
    }

    public function version_resolutions(): HasMany
    {
        return $this->hasMany(VersionResolution::class);
    }
}
