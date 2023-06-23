<?php
declare(strict_types=1);

namespace App\Virtual;

use OpenApi\Attributes as OA;

class DateTimeAtomFormatProperty extends OA\Property
{
    public function __construct(string $property, string $description = 'Date and time')
    {
        parent::__construct(
            property: $property,
            description: $description,
            type: 'string',
            format: 'date-time',
            pattern: '\d{4}-\d{2}-\d{2}\T\d{2}:\d{2}\d{2}\+\d{2}\:\d{2}',
            example: '2023-09-24T15:00:00+00:00',
        );
    }
}
