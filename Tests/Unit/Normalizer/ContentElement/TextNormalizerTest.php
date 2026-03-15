<?php

declare(strict_types=1);

namespace DMF\ContentApi\Tests\Unit\Normalizer\ContentElement;

use DMF\ContentApi\Normalizer\ContentElement\TextNormalizer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TextNormalizerTest extends TestCase
{
    #[Test]
    public function supportsTextCType(): void
    {
        $normalizer = new TextNormalizer();

        self::assertTrue($normalizer->supportsCType('text'));
        self::assertFalse($normalizer->supportsCType('textmedia'));
        self::assertFalse($normalizer->supportsCType('image'));
    }

    #[Test]
    public function normalizesTextElement(): void
    {
        $normalizer = new TextNormalizer();

        $result = $normalizer->normalize([
            'uid' => 42,
            'CType' => 'text',
            'header' => 'My Headline',
            'bodytext' => '<p>Some content</p>',
        ]);

        self::assertSame(42, $result->id);
        self::assertSame('text', $result->type);
        self::assertSame('My Headline', $result->headline);
        self::assertSame(['bodytext' => '<p>Some content</p>'], $result->properties);
        self::assertSame([], $result->media);
    }
}
