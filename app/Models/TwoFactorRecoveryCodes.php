<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 *
 *
 * @property int $user_id
 * @property string $code
 * @property-read User $user
 * @method static Builder<static>|TwoFactorRecoveryCodes newModelQuery()
 * @method static Builder<static>|TwoFactorRecoveryCodes newQuery()
 * @method static Builder<static>|TwoFactorRecoveryCodes query()
 * @mixin Eloquent
 */
class TwoFactorRecoveryCodes extends Pivot
{
    protected $table = "two_factor_codes";

    protected $fillable = [
        'user_id',
        'code'
    ];

    protected $hidden = [
        'code'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
