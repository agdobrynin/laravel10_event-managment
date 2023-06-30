<?php
declare(strict_types=1);

namespace App\Models\Traits;

use App\Dto\LoadRelationAndCountFromRequestDto;
use Illuminate\Database\Eloquent\Builder;

trait LoadRelationsAndCounts
{
    public function scopeRelationsAndCounts(Builder $builder, LoadRelationAndCountFromRequestDto $dto): Builder
    {
        return $builder
            ->when(
                $dto->relation,
                fn(Builder $q) => $q->with($dto->relation)
            )
            ->when(
                $dto->withCount,
                fn(Builder $q) => $q->withCount($dto->withCount)
            );
    }

    public function loadRelationsAndCount(LoadRelationAndCountFromRequestDto $dto): void
    {
        if ($dto->relation) {
            $this->load($dto->relation);
        }

        if ($dto->withCount) {
            $this->loadCount($dto->withCount);
        }
    }
}
