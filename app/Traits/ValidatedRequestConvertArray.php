<?php
declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Str;

trait ValidatedRequestConvertArray
{
    public function validatedToSnake(array $excludeKey = []): array
    {
        $arr = [];

        foreach ($this->validated() as $key => $value) {
            if (!\in_array($key, $excludeKey)) {
                $arr[Str::snake($key)] = $value;
            }
        }

        return $arr;
    }
}
