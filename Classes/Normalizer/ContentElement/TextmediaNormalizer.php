<?php

declare(strict_types=1);

namespace DMF\ContentApi\Normalizer\ContentElement;

use DMF\ContentApi\Dto\ContentElementDto;
use DMF\ContentApi\Dto\MediaDto;
use DMF\ContentApi\Normalizer\ContentElementNormalizerInterface;
use DMF\ContentApi\Normalizer\MediaNormalizer;
use DMF\ContentApi\Query\MediaQueryServiceInterface;

/**
 * Normalizes the TYPO3 'textmedia' content element (CType textmedia).
 *
 * TYPO3 textmedia stores file references in the 'assets' column (sys_file_reference
 * with fieldname='assets'). The imageorient and imagecols fields control layout.
 *
 * imageorient values (TYPO3 standard):
 *   0  = above center,  1  = above right,  2  = above left
 *   8  = below center,  9  = below right,  10 = below left
 *   17 = in text right, 18 = in text left
 *   25 = beside text right, 26 = beside text left
 */
final class TextmediaNormalizer implements ContentElementNormalizerInterface
{
    public function __construct(
        private readonly MediaQueryServiceInterface $mediaQueryService,
        private readonly MediaNormalizer $mediaNormalizer,
    ) {}

    public function supportsCType(string $cType): bool
    {
        return $cType === 'textmedia';
    }

    public function normalize(array $data): ContentElementDto
    {
        $uid = (int) ($data['uid'] ?? 0);

        $mediaData = $this->mediaQueryService->findByContentElementUid($uid, 'assets');
        $media = array_map(
            fn(array $fileData): MediaDto => $this->mediaNormalizer->normalize($fileData),
            $mediaData,
        );

        return new ContentElementDto(
            id: $uid,
            type: 'textmedia',
            headline: (string) ($data['header'] ?? ''),
            properties: [
                'bodytext' => (string) ($data['bodytext'] ?? ''),
                'mediaPosition' => (int) ($data['imageorient'] ?? 0),
                'mediaColumns' => (int) ($data['imagecols'] ?? 1),
            ],
            media: $media,
        );
    }
}
