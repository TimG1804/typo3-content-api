<?php

declare(strict_types=1);

namespace DMF\ContentApi\Query;

use TYPO3\CMS\Core\Database\ConnectionPool;

final class ContentQueryService implements ContentQueryServiceInterface
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {}

    public function findByPageUid(int $pageUid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');

        $rows = $queryBuilder
            ->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageUid, \PDO::PARAM_INT)),
            )
            ->orderBy('sorting', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        return $rows;
    }
}
