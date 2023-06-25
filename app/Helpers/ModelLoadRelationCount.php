<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Dto\LoadRelationAndCountFromRequestDto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ModelLoadRelationCount
{
    public static function load(Model|Builder $for, LoadRelationAndCountFromRequestDto $dto): void
    {
        $load = $for instanceof Model ? 'load' : 'with';
        $loadCount = $for instanceof Model ? 'loadCount' : 'withCount';

        $for
            ->when($dto->relation, fn() => $for->{$load}($dto->relation))
            ->when($dto->withCount, fn() => $for->{$loadCount}($dto->withCount));
    }
}
