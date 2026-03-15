<?php

declare(strict_types=1);

namespace DMF\ContentApi\Tests\Unit\Normalizer;

use DMF\ContentApi\Dto\ContentElementDto;
use DMF\ContentApi\Normalizer\ContentElementNormalizerRegistry;
use DMF\ContentApi\Normalizer\PageNormalizer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class PageNormalizerTest extends TestCase
{
    #[Test]
    public function normalizesPageWithContentElements(): void
    {
        $registry = new ContentElementNormalizerRegistry([], new NullLogger());
        $normalizer = new PageNormalizer($registry);

        $pageData = [
            'uid' => 1,
            'slug' => '/home',
            'title' => 'Homepage',
            'description' => 'Welcome page',
        ];

        $contentElements = [
            ['uid' => 10, 'CType' => 'text', 'header' => 'First'],
            ['uid' => 11, 'CType' => 'image', 'header' => 'Second'],
        ];

        $result = $normalizer->normalize($pageData, $contentElements);

        self::assertSame(1, $result->id);
        self::assertSame('/home', $result->slug);
        self::assertSame('Homepage', $result->title);
        self::assertSame('Welcome page', $result->description);
        self::assertCount(2, $result->content);
        self::assertSame('First', $result->content[0]->headline);
        self::assertSame('Second', $result->content[1]->headline);
    }

    #[Test]
    public function normalizesPageWithEmptyContent(): void
    {
        $registry = new ContentElementNormalizerRegistry([], new NullLogger());
        $normalizer = new PageNormalizer($registry);

        $result = $normalizer->normalize(
            ['uid' => 5, 'slug' => '/empty', 'title' => 'Empty', 'description' => ''],
            [],
        );

        self::assertSame(5, $result->id);
        self::assertSame([], $result->content);
    }
}
