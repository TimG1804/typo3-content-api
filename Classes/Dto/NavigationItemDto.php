<?php

declare(strict_types=1);

namespace DMF\ContentApi\Dto;

final readonly class NavigationItemDto
{
    /**
     * @param NavigationItemDto[] $children
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public string $url,
        public bool $active,
        public bool $current,
        public array $children = [],
    ) {}
}
