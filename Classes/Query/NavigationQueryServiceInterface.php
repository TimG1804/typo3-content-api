<?php

declare(strict_types=1);

namespace DMF\ContentApi\Query;

interface NavigationQueryServiceInterface
{
    /**
     * Build a navigation tree for the given site.
     *
     * @return array<int, array<string, mixed>> Nested menu item arrays with 'children' key
     */
    public function getNavigation(string $siteIdentifier, int $depth = 3): array;
}
