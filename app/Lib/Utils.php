<?php

namespace App\Lib;

use Carbon\CarbonTimeZone;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Throwable;

final class Utils
{
    public static function makeSort(Builder $query, array $fields, ?callable $fallback = null): string
    {
        $fallback = $fallback ?? fn($q) => $q;
        if (!($sort = request()->query->getString('sort'))) {
            $fallback($query);
            return '';
        }

        if (!in_array($sort[0], ['-', '+'])) {
            $fallback($query);
            return '';
        }
        $check = substr($sort, 1);
        $order = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $field = null;

        foreach ($fields as $key => $val) {
            if ($check === (is_string($key) ? $key : $val)) {
                $field = $val;
                break;
            }
        }

        if ($field) {
            $query->orderBy($field, $order);
            return $sort;
        } else {
            $fallback($query);
            return '';
        }
    }

    public static function getClientTimezone(): string
    {
        $tz = request()->header('X-Client-Timezone');
        try {
            return CarbonTimeZone::create($tz) ?? config('app.timezone');
        } catch (Throwable) {
            return config('app.timezone');
        }
    }
}
