<?php

declare(strict_types=1);

namespace DMF\ContentApi\Normalizer\ContentElement;

use DMF\ContentApi\Dto\ContentElementDto;
use DMF\ContentApi\Normalizer\ContentElementNormalizerInterface;

final class TextNormalizer implements ContentElementNormalizerInterface
{
    public function supportsCType(string $cType): bool
    {
        return $cType === 'text';
    }

    public function normalize(array $data): ContentElementDto
    {
        return new ContentElementDto(
            id: (int) ($data['uid'] ?? 0),
            type: 'text',
            headline: (string) ($data['header'] ?? ''),
            properties: [
                'bodytext' => (string) ($data['bodytext'] ?? ''),
            ],
        );
    }
}
