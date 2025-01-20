<?php

namespace App\Models\Concerns;

use Better\Nanoid\Client;

trait HasNanoId
{
    public static function bootHasNanoId(): void
    {
        static::creating(function ($model) {
            $nanoid = new Client();
            $model->attributes[$model->getNanoIdKey()] = $nanoid->produce($model->getNanoIdLength(), true);
        });
    }

    public function getNanoIdKey(): string
    {
        return 'nanoid';
    }

    public function getNanoIdLength(): int
    {
        return 21;
    }

    public function getRouteKeyName(): string
    {
        return $this->getNanoIdKey();
    }
}
