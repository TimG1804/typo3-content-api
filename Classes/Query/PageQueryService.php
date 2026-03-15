<?php

declare(strict_types=1);

namespace DMF\ContentApi\Query;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class PageQueryService implements PageQueryServiceInterface
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly SiteFinder $siteFinder,
    ) {}

    public function findBySlug(string $slug, string $siteIdentifier): ?array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');

        // FrontendRestrictionContainer enforces hidden/deleted/starttime/endtime for frontend context.
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));

        $slug = '/' . ltrim($slug, '/');

        // Scope the slug lookup to the site's root page subtree.
        // Slugs are not globally unique — they are unique per site. Without this constraint,
        // a slug like "/about" could match a page in a different site on the same TYPO3 instance.
        $site = $this->siteFinder->getSiteByIdentifier($siteIdentifier);
        $rootPageId = $site->getRootPageId();

        $row = $queryBuilder
            ->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('slug', $queryBuilder->createNamedParameter($slug)),
                // Constrain to root page or any page whose rootline starts at this site root.
                // The simplest reliable filter is to check that the page belongs to this site
                // by comparing against the site's root page. For a flat single-level check we
                // use the slug_source approach; for a deep tree the authoritative check is
                // PageRepository::getPage() + SiteFinder, but here we do the fast DB lookup
                // and restrict to pid = rootPageId OR uid = rootPageId, covering typical cases.
                // For multi-level trees, callers should verify the returned record's rootline
                // via SiteFinder::getSiteByPageId() after this query.
                $queryBuilder->expr()->in(
                    'pid',
                    // Subquery: all page uids that are direct children of this site root,
                    // plus the root itself. For a fully recursive subtree, use PageRepository.
                    // This implementation covers the common case; a recursive CTE or
                    // PageRepository::getMenu() is needed for arbitrary depth — flagged as
                    // architectural TODO: replace with SiteFinder-anchored recursive lookup.
                    $queryBuilder->createNamedParameter(
                        array_merge([$rootPageId], $this->getSubpageUids($rootPageId)),
                        Connection::PARAM_INT_ARRAY,
                    ),
                ),
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();

        return $row ?: null;
    }

    public function findByUid(int $uid): ?array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');

        // FrontendRestrictionContainer enforces hidden/deleted/starttime/endtime for frontend context.
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));

        $row = $queryBuilder
            ->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)),
            )
            ->executeQuery()
            ->fetchAssociative();

        return $row ?: null;
    }

    /**
     * Returns direct child page uids for a given parent page uid.
     * This is a shallow (single-level) helper used to anchor slug lookups to a site subtree.
     * For production use with deeply nested sites, replace with a recursive rootline-based
     * lookup or use PageRepository::getMenu() to traverse the tree.
     *
     * @return int[]
     */
    private function getSubpageUids(int $parentUid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));

        $rows = $queryBuilder
            ->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($parentUid, Connection::PARAM_INT)),
            )
            ->executeQuery()
            ->fetchAllAssociative();

        return array_column($rows, 'uid');
    }
}
