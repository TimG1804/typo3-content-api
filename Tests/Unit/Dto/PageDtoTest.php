<?php

declare(strict_types=1);

namespace DMF\ContentApi\Tests\Unit\Dto;

use DMF\ContentApi\Dto\AccessDto;
use DMF\ContentApi\Dto\ContentElementDto;
use DMF\ContentApi\Dto\PageDto;
use DMF\ContentApi\Dto\SeoDto;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PageDtoTest extends TestCase
{
    #[Test]
    public function constructorSetsAllProperties(): void
    {
        $content = [
            new ContentElementDto(id: 1, type: 'text', headline: 'Hello', properties: ['bodytext' => 'World']),
        ];
        $seo = new SeoDto(
            title: 'SEO Title',
            robots: 'index,follow',
            canonicalUrl: null,
            ogTitle: '',
            ogDescription: '',
        );
        $access = new AccessDto(feGroups: [], starttime: null, endtime: null, extendToSubpages: false);

        $dto = new PageDto(
            id: 42,
            slug: '/home',
            title: 'Homepage',
            navTitle: 'Home',
            description: 'The homepage',
            doktype: 1,
            updatedAt: 1700000000,
            seo: $seo,
            access: $access,
            content: $content,
        );

        self::assertSame(42, $dto->id);
        self::assertSame('/home', $dto->slug);
        self::assertSame('Homepage', $dto->title);
        self::assertSame('Home', $dto->navTitle);
        self::assertSame('The homepage', $dto->description);
        self::assertSame(1, $dto->doktype);
        self::assertSame(1700000000, $dto->updatedAt);
        self::assertSame($seo, $dto->seo);
        self::assertSame($access, $dto->access);
        self::assertCount(1, $dto->content);
        self::assertSame('text', $dto->content[0]->type);
    }

    #[Test]
    public function seoIsNullableWhenExtSeoNotInstalled(): void
    {
        $access = new AccessDto(feGroups: [], starttime: null, endtime: null, extendToSubpages: false);

        $dto = new PageDto(
            id: 1,
            slug: '/test',
            title: 'Test',
            navTitle: '',
            description: '',
            doktype: 1,
            updatedAt: 0,
            seo: null,
            access: $access,
            content: [],
        );

        self::assertNull($dto->seo);
    }
}
