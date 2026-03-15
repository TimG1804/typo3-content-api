<?php

declare(strict_types=1);

namespace DMF\ContentApi\Dto;

final readonly class MediaDto
{
    public function __construct(
        public int $id,
        public string $url,
        public string $mimeType,
        public string $title,
        public string $alt,
        public ?int $width,
        public ?int $height,
    ) {}
}
