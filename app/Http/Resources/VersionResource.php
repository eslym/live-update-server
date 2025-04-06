<?php

namespace App\Http\Resources;

use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Version
 */
class VersionResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nanoid' => $this->nanoid,
            'channels' => $this->channels->map(fn($ch) => $ch->channel?->name),
            'reqs' => $this->requirements,
            'name' => $this->name,
            'created_at' => $this->created_at,
        ];
    }
}
