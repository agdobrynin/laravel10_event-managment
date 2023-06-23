<?php
declare(strict_types=1);

namespace App\Virtual;

use OpenApi\Attributes as OA;

#[OA\Schema(
    properties: [
        new OA\Property(
            property: 'meta',
            properties: [
                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                new OA\Property(property: 'from', type: 'integer', example: 1),
                new OA\Property(property: 'to', type: 'integer', example: 12),
                new OA\Property(property: 'last_page', type: 'integer', example: 9),
                new OA\Property(property: 'path', type: 'string', format: 'url', example: 'http://localhost/api/path'),
                new OA\Property(property: 'per_page', type: 'integer', example: 12),
                new OA\Property(property: 'total', type: 'integer', example: 100),
                new OA\Property(
                    property: 'links',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'active', type: 'boolean', example: true),
                            new OA\Property(property: 'label', type: 'string', example: '1'),
                            new OA\Property(property: 'url', type: 'string', format: 'url', example: 'http://localhost/api/path?page=1', nullable: true),
                        ]
                    ),
                    minItems: 1,
                )
            ],
            type: 'object',
        )
    ]
)]
class PaginateMeta
{
}
