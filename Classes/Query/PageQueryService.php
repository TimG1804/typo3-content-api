<?php

declare(strict_types=1);

namespace DMF\ContentApi\Query;

use TYPO3\CMS\Core\Database\ConnectionPool;

final class PageQueryService implements PageQueryServiceInterface
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {}

    public function findBySlug(string $slug, string $siteIdentifier): ?array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');

        $slug = '/' . ltrim($slug, '/');

        $row = $queryBuilder
            ->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('slug', $queryBuilder->createNamedParameter($slug)),
            )
            ->executeQuery()
            ->fetchAssociative();

        return $row ?: null;
    }

    public function findByUid(int $uid): ?array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');

        $row = $queryBuilder
            ->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)),
            )
            ->executeQuery()
            ->fetchAssociative();

        return $row ?: null;
    }
}
