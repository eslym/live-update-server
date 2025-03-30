<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $request->user();

        return [
            ...parent::toArray($request),
            'is_mutable' => $user->id !== $this->id && $user->is_superadmin,
            'is_2fa_enabled' => $this->google2fa_secret !== null,
        ];
    }
}
