<?php

declare(strict_types=1);

namespace DMF\ContentApi\Query;

interface ContentQueryServiceInterface
{
    /**
     * Find all content elements for a given page.
     *
     * @return array<int, array<string, mixed>> Raw tt_content records, sorted by sorting
     */
    public function findByPageUid(int $pageUid): array;
}
