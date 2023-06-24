<?php
declare(strict_types=1);

namespace App\Virtual;

use App\Dto\ApiErrorResponseDto;
use Attribute;
use OpenApi\Attributes as OA;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class HttpUnauthorizedResponse extends OA\Response
{
    public function __construct(string $description = 'Unauthorized')
    {
        parent::__construct(
            response: 401,
            description: $description,
            content: [
                new OA\JsonContent(ref: ApiErrorResponseDto::class),
            ]
        );
    }
}
