<?php

namespace App\Models;

use Better\Nanoid\Client;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 * @method static Builder<static>|Version newModelQuery()
 * @method static Builder<static>|Version newQuery()
 * @method static Builder<static>|Version query()
 * @mixin Eloquent
 */
class Version extends Model
{
    protected $fillable = ['name', 'project_id', 'path', 'signature', 'android_requirements', 'ios_requirements'];

    protected static function boot(): void
    {
        parent::boot();
        self::creating(function ($model) {
            $nanoid = new Client();
            $model->nanoid = $nanoid->produce(21, true);
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function getRouteKeyName(): string
    {
        return 'nanoid';
    }
}
