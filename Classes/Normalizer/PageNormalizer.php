<?php

declare(strict_types=1);

namespace DMF\ContentApi\Normalizer;

use DMF\ContentApi\Dto\AccessDto;
use DMF\ContentApi\Dto\ContentElementDto;
use DMF\ContentApi\Dto\PageDto;
use DMF\ContentApi\Dto\SeoDto;

final class PageNormalizer
{
    public function __construct(
        private readonly ContentElementNormalizerRegistry $contentElementNormalizerRegistry,
    ) {}

    /**
     * @param array<string, mixed> $pageData Raw pages record
     * @param array<int, array<string, mixed>> $contentElements Raw tt_content records
     */
    public function normalize(array $pageData, array $contentElements): PageDto
    {
        $normalizedContent = array_map(
            fn(array $element): ContentElementDto => $this->contentElementNormalizerRegistry->normalize($element),
            $contentElements,
        );

        return new PageDto(
            id: (int) ($pageData['uid'] ?? 0),
            slug: (string) ($pageData['slug'] ?? ''),
            title: (string) ($pageData['title'] ?? ''),
            navTitle: (string) ($pageData['nav_title'] ?? ''),
            description: (string) ($pageData['description'] ?? ''),
            doktype: (int) ($pageData['doktype'] ?? 1),
            updatedAt: $this->resolveUpdatedAt($pageData),
            seo: $this->normalizeSeo($pageData),
            access: $this->normalizeAccess($pageData),
            content: $normalizedContent,
        );
    }

    /**
     * Returns lastUpdated if explicitly set by editors, otherwise falls back to the
     * record's own modification timestamp (tstamp).
     */
    private function resolveUpdatedAt(array $pageData): int
    {
        $lastUpdated = (int) ($pageData['lastUpdated'] ?? 0);
        if ($lastUpdated > 0) {
            return $lastUpdated;
        }

        return (int) ($pageData['tstamp'] ?? 0);
    }

    /**
     * Builds SeoDto from EXT:seo fields.
     * Returns null when EXT:seo is not installed (fields absent from page record).
     *
     * @param array<string, mixed> $pageData
     */
    private function normalizeSeo(array $pageData): ?SeoDto
    {
        if (!\array_key_exists('seo_title', $pageData)) {
            return null;
        }

        $seoTitle = (string) ($pageData['seo_title'] ?? '');
        if ($seoTitle === '') {
            $seoTitle = (string) ($pageData['title'] ?? '');
        }

        $noIndex = (bool) ($pageData['no_index'] ?? false);
        $noFollow = (bool) ($pageData['no_follow'] ?? false);
        $robots = ($noIndex ? 'noindex' : 'index') . ',' . ($noFollow ? 'nofollow' : 'follow');

        $canonicalLink = (string) ($pageData['canonical_link'] ?? '');

        return new SeoDto(
            title: $seoTitle,
            robots: $robots,
            canonicalUrl: $canonicalLink !== '' ? $canonicalLink : null,
            ogTitle: (string) ($pageData['og_title'] ?? ''),
            ogDescription: (string) ($pageData['og_description'] ?? ''),
        );
    }

    /**
     * @param array<string, mixed> $pageData
     */
    private function normalizeAccess(array $pageData): AccessDto
    {
        $feGroupRaw = (string) ($pageData['fe_group'] ?? '');
        $feGroups = $feGroupRaw !== ''
            ? array_map('intval', explode(',', $feGroupRaw))
            : [];

        $starttime = (int) ($pageData['starttime'] ?? 0);
        $endtime = (int) ($pageData['endtime'] ?? 0);

        return new AccessDto(
            feGroups: $feGroups,
            starttime: $starttime > 0 ? $starttime : null,
            endtime: $endtime > 0 ? $endtime : null,
            extendToSubpages: (bool) ($pageData['extendToSubpages'] ?? false),
        );
    }
}
