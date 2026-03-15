<?php

declare(strict_types=1);

namespace DMF\ContentApi\Dto;

final readonly class ErrorDto
{
    public function __construct(
        public int $status,
        public string $error,
        public string $message,
    ) {}
}
