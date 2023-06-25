<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Dto\LoadRelationAndCountFromRequestDto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ModelLoadRelationCount
{
    public static function load(Model|Builder $for, LoadRelationAndCountFromRequestDto $dto): Model|Builder
    {
        return $for
            ->when(
                $dto->relation,
                fn($q) => $for instanceof Model ? $for->load($dto->relation) : $q->with($dto->relation)
            )
            ->when(
                $dto->withCount,
                fn($q) => $for instanceof Model ? $for->loadCount($dto->withCount) : $q->withCount($dto->withCount)
            );
    }
}
