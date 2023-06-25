<?php
declare(strict_types=1);

namespace App\Models\Traits;

use App\Dto\LoadRelationAndCountFromRequestDto;
use Illuminate\Database\Eloquent\Builder;

trait LoadRelationsAndCounts
{
    public function scopeLoadRelationsAndCounts(Builder $builder, LoadRelationAndCountFromRequestDto $dto): Builder
    {
        return $builder
            ->when(
                $dto->relation,
                fn($q) => $q->with($dto->relation)
            )
            ->when(
                $dto->withCount,
                fn($q) => $q->withCount($dto->withCount)
            );
    }
}
