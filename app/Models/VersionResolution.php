<?php

namespace App\Models;

use Composer\Semver\Semver;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 
 *
 * @property int $id
 * @property int $project_id
 * @property string $platform
 * @property string $app_version
 * @property int|null $version_id
 * @property bool $needs_reindex
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Project $project
 * @property-read \App\Models\Version|null $version
 * @method static Builder<static>|VersionResolution newModelQuery()
 * @method static Builder<static>|VersionResolution newQuery()
 * @method static Builder<static>|VersionResolution query()
 * @mixin Eloquent
 */
class VersionResolution extends Model
{
    protected $fillable = ['project_id', 'platform', 'app_version', 'version_id', 'needs_reindex'];

    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'needs_reindex' => 'boolean',
        ];
    }

    public function reIndex(&$versions = null): void
    {
        if (!$versions) {
            $versions = Version::query()->where('project_id', $this->project_id);
            if ($this->platform === 'ios') {
                $versions->whereNotNull('ios_requirements');
            } else {
                $versions->whereNotNull('android_requirements');
            }
            $versions = $versions->orderByDesc('created_at')->get(['id', 'ios_requirements', 'android_requirements']);
        }

        $version = null;
        foreach ($versions as $v) {
            if ($this->platform === 'ios' && Semver::satisfies($this->app_version, $v->ios_requirements)) {
                $version = $v;
                break;
            } elseif ($this->platform === 'android' && Semver::satisfies($this->app_version, $v->android_requirements)) {
                $version = $v;
                break;
            }
        }

        $this->version_id = $version?->id;
        $this->needs_reindex = false;
        $this->save();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(Version::class);
    }
}
