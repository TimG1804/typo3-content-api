<?php

declare(strict_types=1);

namespace DMF\ContentApi\Dto;

final readonly class NavigationDto
{
    /**
     * @param NavigationItemDto[] $items
     */
    public function __construct(
        public string $key,
        public array $items,
    ) {}
}
