<?php

declare(strict_types=1);

namespace DMF\ContentApi\Normalizer;

use DMF\ContentApi\Dto\MediaDto;

final class MediaNormalizer
{
    /**
     * @param array<string, mixed> $fileData FAL file reference data
     */
    public function normalize(array $fileData): MediaDto
    {
        return new MediaDto(
            id: (int)($fileData['uid'] ?? 0),
            url: (string)($fileData['url'] ?? ''),
            mimeType: (string)($fileData['mime_type'] ?? ''),
            title: (string)($fileData['title'] ?? ''),
            alt: (string)($fileData['alternative'] ?? ''),
            width: isset($fileData['width']) ? (int)$fileData['width'] : null,
            height: isset($fileData['height']) ? (int)$fileData['height'] : null,
        );
    }
}
