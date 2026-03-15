<?php

declare(strict_types=1);

namespace DMF\ContentApi\Query;

interface ContentQueryServiceInterface
{
    /**
     * Find all content elements for a given page.
     *
     * Returns default-language (sys_language_uid = 0) tt_content records sorted by colPos then
     * sorting. Language overlays must be applied by the calling layer using PageRepository after
     * this query when $languageUid > 0.
     *
     * @param int $languageUid The target language uid. Passed through for callers that apply overlays.
     * @return array<int, array<string, mixed>> Raw tt_content records, sorted by colPos then sorting
     */
    public function findByPageUid(int $pageUid, int $languageUid = 0): array;
}
