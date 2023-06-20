<?php
declare(strict_types=1);

namespace App\Dto;

readonly class ApiErrorResponseDto
{
    public function __construct(public string $message)
    {
    }
}
