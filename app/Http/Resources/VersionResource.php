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
            'channel' => $this->channel,
            'reqs' => $this->requirements,
            'name' => $this->name,
            'created_at' => $this->created_at,
        ];
    }
}
