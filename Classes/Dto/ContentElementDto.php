<?php

declare(strict_types=1);

namespace DMF\ContentApi\Dto;

final readonly class ContentElementDto
{
    /**
     * @param array<string, mixed> $properties
     * @param MediaDto[] $media
     */
    public function __construct(
        public int $id,
        public string $type,
        public string $headline,
        public array $properties,
        public array $media = [],
    ) {}
}
