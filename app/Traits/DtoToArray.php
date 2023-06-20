<?php
declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Str;

trait DtoToArray
{
    public function scalarOrNullValueToSnakeKeyArray(array $excludeKey = []): array
    {
        $arr = [];

        foreach ((array)$this as $key => $value) {
            if (!\in_array($key, $excludeKey) && (is_scalar($value) || is_null($value) )) {
                $arr[Str::snake($key)] = $value;
            }
        }

        return $arr;
    }
}
