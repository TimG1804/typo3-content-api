<?php

declare(strict_types=1);

namespace DMF\ContentApi\Query;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class ContentQueryService implements ContentQueryServiceInterface
{
    public function __construct(
        private ConnectionPool $connectionPool,
    ) {}

    public function findByPageUid(int $pageUid, int $languageUid = 0): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');

        // FrontendRestrictionContainer enforces hidden/deleted/starttime/endtime for frontend context.
        // Without this, hidden content elements would be returned to API consumers.
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));

        // Always query default-language records (sys_language_uid = 0).
        // Language overlays must be applied after this query using PageRepository::getRecordOverlay()
        // (v12) or PageRepository::getLanguageOverlay() (v13) when $languageUid > 0.
        // Fetching raw translation records (sys_language_uid > 0) directly is incorrect because
        // TYPO3's overlay mechanism handles fallback chains, hide-if-not-translated, etc.
        // The $languageUid parameter is passed through to callers who need to apply overlays.
        $rows = $queryBuilder
            ->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageUid, Connection::PARAM_INT)),
                // Query default language records; overlay is applied by the normalizer layer
                // or a dedicated overlay service, not here at the raw query level.
                $queryBuilder->expr()->eq(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT),
                ),
            )
            ->orderBy('colPos', 'ASC')
            ->addOrderBy('sorting', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        return $rows;
    }
}
