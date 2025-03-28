<?php

namespace App\Models;

use App\Models\Concerns\HasNanoId;
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
 * @property int|null $channel_id
 * @property string $name
 * @property string $path
 * @property string|null $signature
 * @property bool $android_available
 * @property int|null $android_min
 * @property int|null $android_max
 * @property bool $ios_available
 * @property int|null $ios_min
 * @property int|null $ios_max
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Channel|null $channel
 * @property-read array $requirements
 * @property-read Project $project
 * @method static Builder<static>|Version newModelQuery()
 * @method static Builder<static>|Version newQuery()
 * @method static Builder<static>|Version query()
 * @mixin Eloquent
 */
class Version extends Model
{
    use HasNanoId;

    protected $fillable = [
        'name', 'project_id', 'channel_id', 'path', 'signature',
        'android_available', 'ios_available',
        'android_min', 'android_max', 'ios_min', 'ios_max'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function getRequirementsAttribute(): array
    {
        return [
            'android' => $this->android_available ? [
                'min' => $this->android_min,
                'max' => $this->android_max,
            ] : null,
            'ios' => $this->ios_available ? [
                'min' => $this->ios_min,
                'max' => $this->ios_max,
            ] : null,
        ];
    }

    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'android_available' => 'boolean',
            'ios_available' => 'boolean',
        ];
    }
}
