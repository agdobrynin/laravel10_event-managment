<?php
declare(strict_types=1);

namespace App\Dto;

use OpenApi\Attributes as OA;

#[OA\Schema]
readonly class AuthSuccessDto
{
    public function __construct(
        #[OA\Property(title: 'API Token')]
        public string $token
    )
    {
    }
}
