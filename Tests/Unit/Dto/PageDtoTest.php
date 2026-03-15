<?php

declare(strict_types=1);

namespace DMF\ContentApi\Tests\Unit\Dto;

use DMF\ContentApi\Dto\ContentElementDto;
use DMF\ContentApi\Dto\PageDto;
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

        $dto = new PageDto(
            id: 42,
            slug: '/home',
            title: 'Homepage',
            description: 'The homepage',
            content: $content,
        );

        self::assertSame(42, $dto->id);
        self::assertSame('/home', $dto->slug);
        self::assertSame('Homepage', $dto->title);
        self::assertSame('The homepage', $dto->description);
        self::assertCount(1, $dto->content);
        self::assertSame('text', $dto->content[0]->type);
    }
}
