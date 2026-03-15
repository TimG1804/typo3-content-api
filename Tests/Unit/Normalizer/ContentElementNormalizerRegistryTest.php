<?php

declare(strict_types=1);

namespace DMF\ContentApi\Tests\Unit\Normalizer;

use DMF\ContentApi\Dto\ContentElementDto;
use DMF\ContentApi\Normalizer\ContentElementNormalizerInterface;
use DMF\ContentApi\Normalizer\ContentElementNormalizerRegistry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class ContentElementNormalizerRegistryTest extends TestCase
{
    #[Test]
    public function matchingNormalizerIsUsed(): void
    {
        $normalizer = new class implements ContentElementNormalizerInterface {
            public function supportsCType(string $cType): bool
            {
                return $cType === 'text';
            }

            public function normalize(array $data): ContentElementDto
            {
                return new ContentElementDto(
                    id: (int)$data['uid'],
                    type: 'text',
                    headline: $data['header'],
                    properties: ['bodytext' => $data['bodytext']],
                );
            }
        };

        $registry = new ContentElementNormalizerRegistry([$normalizer], new NullLogger());

        $result = $registry->normalize([
            'uid' => 1,
            'CType' => 'text',
            'header' => 'Test',
            'bodytext' => 'Hello',
        ]);

        self::assertSame('text', $result->type);
        self::assertSame('Test', $result->headline);
        self::assertSame(['bodytext' => 'Hello'], $result->properties);
    }

    #[Test]
    public function fallbackIsUsedForUnknownCType(): void
    {
        $registry = new ContentElementNormalizerRegistry([], new NullLogger());

        $result = $registry->normalize([
            'uid' => 99,
            'CType' => 'unknown_element',
            'header' => 'Some header',
        ]);

        self::assertSame(99, $result->id);
        self::assertSame('unknown_element', $result->type);
        self::assertSame('Some header', $result->headline);
        self::assertSame([], $result->properties);
    }

    #[Test]
    public function firstMatchingNormalizerWins(): void
    {
        $first = new class implements ContentElementNormalizerInterface {
            public function supportsCType(string $cType): bool
            {
                return $cType === 'text';
            }

            public function normalize(array $data): ContentElementDto
            {
                return new ContentElementDto(id: 1, type: 'text', headline: 'first', properties: []);
            }
        };

        $second = new class implements ContentElementNormalizerInterface {
            public function supportsCType(string $cType): bool
            {
                return $cType === 'text';
            }

            public function normalize(array $data): ContentElementDto
            {
                return new ContentElementDto(id: 1, type: 'text', headline: 'second', properties: []);
            }
        };

        $registry = new ContentElementNormalizerRegistry([$first, $second], new NullLogger());

        $result = $registry->normalize(['uid' => 1, 'CType' => 'text', 'header' => '']);

        self::assertSame('first', $result->headline);
    }
}
