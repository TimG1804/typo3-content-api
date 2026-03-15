<?php

declare(strict_types=1);

namespace DMF\ContentApi\Normalizer;

use DMF\ContentApi\Dto\NavigationDto;
use DMF\ContentApi\Dto\NavigationItemDto;

final class NavigationNormalizer
{
    /**
     * @param array<int, array<string, mixed>> $menuItems Raw menu data from TYPO3
     */
    public function normalize(string $key, array $menuItems): NavigationDto
    {
        return new NavigationDto(
            key: $key,
            items: array_map($this->normalizeItem(...), $menuItems),
        );
    }

    /**
     * @param array<string, mixed> $item
     */
    private function normalizeItem(array $item): NavigationItemDto
    {
        $children = $item['children'] ?? [];

        return new NavigationItemDto(
            id: (int)($item['uid'] ?? 0),
            title: (string)($item['title'] ?? ''),
            slug: (string)($item['slug'] ?? ''),
            url: (string)($item['url'] ?? ''),
            active: (bool)($item['active'] ?? false),
            current: (bool)($item['current'] ?? false),
            children: array_map($this->normalizeItem(...), $children),
        );
    }
}
