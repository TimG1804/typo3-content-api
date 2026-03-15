<?php

declare(strict_types=1);

namespace DMF\ContentApi\Dto;

final readonly class PageDto
{
    /**
     * @param ContentElementDto[] $content
     */
    public function __construct(
        public int $id,
        public string $slug,
        public string $title,
        public string $description,
        public array $content,
    ) {}
}
