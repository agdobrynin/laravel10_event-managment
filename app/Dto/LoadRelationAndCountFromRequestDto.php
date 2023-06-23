<?php
declare(strict_types=1);

namespace App\Dto;

readonly class LoadRelationAndCountFromRequestDto
{
    public function __construct(
        /**
         * Eloquent notation relation names
         * @var string[]|null
         */
        public ?array $relation = null,
        /**
         * Eloquent notation field names
         * @var string[]|null
         */
        public ?array $withCount = null
    )
    {
    }
}
