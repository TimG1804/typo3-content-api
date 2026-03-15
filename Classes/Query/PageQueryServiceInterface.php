<?php

declare(strict_types=1);

namespace DMF\ContentApi\Query;

interface PageQueryServiceInterface
{
    /**
     * Find a page by its slug within the given site.
     *
     * @return array<string, mixed>|null Raw pages record or null if not found
     */
    public function findBySlug(string $slug, string $siteIdentifier): ?array;

    /**
     * Find a page by its uid.
     *
     * @return array<string, mixed>|null Raw pages record or null if not found
     */
    public function findByUid(int $uid): ?array;
}
