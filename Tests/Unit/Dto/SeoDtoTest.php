<?php

declare(strict_types=1);

namespace DMF\ContentApi\Tests\Unit\Dto;

use DMF\ContentApi\Dto\SeoDto;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SeoDtoTest extends TestCase
{
    #[Test]
    public function constructorSetsAllProperties(): void
    {
        $dto = new SeoDto(
            title: 'SEO Title',
            robots: 'noindex,nofollow',
            canonicalUrl: 'https://example.com/page',
            ogTitle: 'OG Title',
            ogDescription: 'OG Description',
        );

        self::assertSame('SEO Title', $dto->title);
        self::assertSame('noindex,nofollow', $dto->robots);
        self::assertSame('https://example.com/page', $dto->canonicalUrl);
        self::assertSame('OG Title', $dto->ogTitle);
        self::assertSame('OG Description', $dto->ogDescription);
    }

    #[Test]
    public function canonicalUrlIsNullable(): void
    {
        $dto = new SeoDto(
            title: 'Title',
            robots: 'index,follow',
            canonicalUrl: null,
            ogTitle: '',
            ogDescription: '',
        );

        self::assertNull($dto->canonicalUrl);
    }
}
