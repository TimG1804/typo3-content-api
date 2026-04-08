<?php

declare(strict_types=1);

namespace DMF\ContentApi\Tests\Unit\Serializer;

use DMF\ContentApi\Dto\AccessDto;
use DMF\ContentApi\Dto\ContentElementDto;
use DMF\ContentApi\Dto\ErrorDto;
use DMF\ContentApi\Dto\MetaDto;
use DMF\ContentApi\Dto\PageDto;
use DMF\ContentApi\Dto\PageResponseDto;
use DMF\ContentApi\Serializer\JsonSerializer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class JsonSerializerTest extends TestCase
{
    private JsonSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new JsonSerializer();
    }

    #[Test]
    public function serializesErrorDto(): void
    {
        $dto = new ErrorDto(status: 404, error: 'Not Found', message: 'Page not found');

        $json = $this->serializer->serialize($dto);
        $decoded = json_decode($json, true);

        self::assertSame(404, $decoded['status']);
        self::assertSame('Not Found', $decoded['error']);
        self::assertSame('Page not found', $decoded['message']);
    }

    #[Test]
    public function serializesPageResponseDto(): void
    {
        $meta = new MetaDto(apiVersion: '1.0', language: 'en', site: 'default');
        $content = [
            new ContentElementDto(id: 1, type: 'text', headline: 'Hello', properties: ['bodytext' => 'World']),
        ];
        $access = new AccessDto(feGroups: [], starttime: null, endtime: null, extendToSubpages: false);
        $page = new PageDto(
            id: 42,
            slug: '/home',
            title: 'Homepage',
            navTitle: '',
            description: '',
            doktype: 1,
            updatedAt: 0,
            seo: null,
            access: $access,
            content: $content,
        );
        $response = new PageResponseDto(meta: $meta, page: $page);

        $json = $this->serializer->serialize($response);
        $decoded = json_decode($json, true);

        self::assertSame('1.0', $decoded['meta']['apiVersion']);
        self::assertSame(42, $decoded['page']['id']);
        self::assertSame('text', $decoded['page']['content'][0]['type']);
        self::assertSame('World', $decoded['page']['content'][0]['properties']['bodytext']);
    }
}
