<?php

declare(strict_types=1);

namespace DMF\ContentApi\Normalizer;

use DMF\ContentApi\Dto\ContentElementDto;

/**
 * Implement this interface to provide normalization for a specific CType.
 *
 * Register your normalizer by tagging it with 'content_api.content_element_normalizer'
 * in your extension's Services.yaml.
 */
interface ContentElementNormalizerInterface
{
    /**
     * Returns true if this normalizer handles the given CType.
     */
    public function supportsCType(string $cType): bool;

    /**
     * Normalize a tt_content record array into a ContentElementDto.
     *
     * @param array<string, mixed> $data Raw tt_content record
     */
    public function normalize(array $data): ContentElementDto;
}
