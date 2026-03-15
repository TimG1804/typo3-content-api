<?php

declare(strict_types=1);

namespace DMF\ContentApi\Normalizer;

use DMF\ContentApi\Dto\ContentElementDto;
use DMF\ContentApi\Dto\PageDto;

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
            id: (int)($pageData['uid'] ?? 0),
            slug: (string)($pageData['slug'] ?? ''),
            title: (string)($pageData['title'] ?? ''),
            description: (string)($pageData['description'] ?? ''),
            content: $normalizedContent,
        );
    }
}
