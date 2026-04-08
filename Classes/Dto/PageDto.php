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
        public string $navTitle,
        public string $description,
        public int $doktype,
        public int $updatedAt,
        public ?SeoDto $seo,
        public AccessDto $access,
        public array $content,
    ) {}
}
