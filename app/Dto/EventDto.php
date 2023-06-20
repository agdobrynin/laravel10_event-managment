<?php
declare(strict_types=1);

namespace App\Dto;

use App\Traits\DtoToArray;

readonly class EventDto
{

    use DtoToArray;

    public function __construct(
        public string  $name,
        public string  $startTime,
        public string  $endTime,
        public ?string $description = null,
    )
    {
    }
}
