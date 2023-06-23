<?php
declare(strict_types=1);

namespace App\Virtual;

use OpenApi\Attributes as OA;

class PropertyShortDateTime extends OA\Property
{
    public function __construct(string $property, string $description = '')
    {
        parent::__construct(
            property: $property,
            description: $description,
            type: 'string',
            pattern: '^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$',
            example: '2023-01-01 11:30'
        );
    }
}
