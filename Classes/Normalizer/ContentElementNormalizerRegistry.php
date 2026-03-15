<?php

declare(strict_types=1);

namespace DMF\ContentApi\Normalizer;

use DMF\ContentApi\Dto\ContentElementDto;
use Psr\Log\LoggerInterface;

final class ContentElementNormalizerRegistry
{
    /** @var iterable<ContentElementNormalizerInterface> */
    private readonly iterable $normalizers;

    /**
     * @param iterable<ContentElementNormalizerInterface> $normalizers
     */
    public function __construct(
        iterable $normalizers,
        private readonly LoggerInterface $logger,
    ) {
        $this->normalizers = $normalizers;
    }

    /**
     * @param array<string, mixed> $data Raw tt_content record
     */
    public function normalize(array $data): ContentElementDto
    {
        $cType = $data['CType'] ?? '';

        foreach ($this->normalizers as $normalizer) {
            if ($normalizer->supportsCType($cType)) {
                return $normalizer->normalize($data);
            }
        }

        $this->logger->notice('No normalizer registered for CType "{ctype}", using fallback.', [
            'ctype' => $cType,
        ]);

        return $this->fallback($data, $cType);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function fallback(array $data, string $cType): ContentElementDto
    {
        return new ContentElementDto(
            id: (int)($data['uid'] ?? 0),
            type: $cType,
            headline: (string)($data['header'] ?? ''),
            properties: [],
        );
    }
}
