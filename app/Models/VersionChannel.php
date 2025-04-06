<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 *
 *
 * @property int $version_id
 * @property int|null $channel_id
 * @property-read Channel|null $channel
 * @property-read Version $version
 * @method static Builder<static>|VersionChannel newModelQuery()
 * @method static Builder<static>|VersionChannel newQuery()
 * @method static Builder<static>|VersionChannel query()
 * @mixin Eloquent
 */
class VersionChannel extends Pivot
{
    protected $table = 'version_channel';

    protected $fillable = [
        'version_id',
        'channel_id',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(Version::class);
    }
}
