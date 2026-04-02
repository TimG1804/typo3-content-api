<?php

declare(strict_types=1);

namespace DMF\ContentApi\Tests\Unit\Normalizer\ContentElement;

use DMF\ContentApi\Dto\MediaDto;
use DMF\ContentApi\Normalizer\ContentElement\TextmediaNormalizer;
use DMF\ContentApi\Normalizer\MediaNormalizer;
use DMF\ContentApi\Query\MediaQueryServiceInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TextmediaNormalizerTest extends TestCase
{
    private MediaQueryServiceInterface $mediaQueryService;
    private TextmediaNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->mediaQueryService = $this->createMock(MediaQueryServiceInterface::class);
        $this->normalizer = new TextmediaNormalizer($this->mediaQueryService, new MediaNormalizer());
    }

    #[Test]
    public function supportsTextmediaCType(): void
    {
        self::assertTrue($this->normalizer->supportsCType('textmedia'));
        self::assertFalse($this->normalizer->supportsCType('text'));
        self::assertFalse($this->normalizer->supportsCType('image'));
        self::assertFalse($this->normalizer->supportsCType(''));
    }

    #[Test]
    public function normalizesTextmediaWithoutMedia(): void
    {
        $this->mediaQueryService
            ->expects(self::once())
            ->method('findByContentElementUid')
            ->with(42, 'assets')
            ->willReturn([]);

        $result = $this->normalizer->normalize([
            'uid' => 42,
            'CType' => 'textmedia',
            'header' => 'My Headline',
            'bodytext' => '<p>Some content</p>',
            'imageorient' => 0,
            'imagecols' => 2,
        ]);

        self::assertSame(42, $result->id);
        self::assertSame('textmedia', $result->type);
        self::assertSame('My Headline', $result->headline);
        self::assertSame('<p>Some content</p>', $result->properties['bodytext']);
        self::assertSame(0, $result->properties['mediaPosition']);
        self::assertSame(2, $result->properties['mediaColumns']);
        self::assertSame([], $result->media);
    }

    #[Test]
    public function normalizesTextmediaWithSingleMediaItem(): void
    {
        $this->mediaQueryService
            ->expects(self::once())
            ->method('findByContentElementUid')
            ->with(10, 'assets')
            ->willReturn([
                [
                    'uid' => 1,
                    'url' => '/fileadmin/image.jpg',
                    'mime_type' => 'image/jpeg',
                    'title' => 'An image',
                    'alternative' => 'Alt text',
                    'width' => 800,
                    'height' => 600,
                ],
            ]);

        $result = $this->normalizer->normalize([
            'uid' => 10,
            'header' => 'With Media',
            'bodytext' => '<p>Text beside image</p>',
            'imageorient' => 17,
            'imagecols' => 1,
        ]);

        self::assertSame(10, $result->id);
        self::assertCount(1, $result->media);
        self::assertInstanceOf(MediaDto::class, $result->media[0]);
        self::assertSame(1, $result->media[0]->id);
        self::assertSame('/fileadmin/image.jpg', $result->media[0]->url);
        self::assertSame('image/jpeg', $result->media[0]->mimeType);
        self::assertSame('An image', $result->media[0]->title);
        self::assertSame('Alt text', $result->media[0]->alt);
        self::assertSame(800, $result->media[0]->width);
        self::assertSame(600, $result->media[0]->height);
        self::assertSame(17, $result->properties['mediaPosition']);
        self::assertSame(1, $result->properties['mediaColumns']);
    }

    #[Test]
    public function normalizesTextmediaWithMultipleMediaItems(): void
    {
        $this->mediaQueryService
            ->expects(self::once())
            ->method('findByContentElementUid')
            ->willReturn([
                ['uid' => 1, 'url' => '/img1.jpg', 'mime_type' => 'image/jpeg', 'title' => '', 'alternative' => '', 'width' => 400, 'height' => 300],
                ['uid' => 2, 'url' => '/img2.png', 'mime_type' => 'image/png', 'title' => 'Second', 'alternative' => 'Alt', 'width' => null, 'height' => null],
                ['uid' => 3, 'url' => '/video.mp4', 'mime_type' => 'video/mp4', 'title' => 'Video', 'alternative' => '', 'width' => null, 'height' => null],
            ]);

        $result = $this->normalizer->normalize([
            'uid' => 7,
            'header' => 'Gallery',
            'bodytext' => '',
            'imageorient' => 0,
            'imagecols' => 3,
        ]);

        self::assertCount(3, $result->media);
        self::assertSame(1, $result->media[0]->id);
        self::assertSame(2, $result->media[1]->id);
        self::assertSame(3, $result->media[2]->id);
        self::assertSame('video/mp4', $result->media[2]->mimeType);
        self::assertSame(3, $result->properties['mediaColumns']);
    }

    #[Test]
    public function normalizesMediaWithNullDimensions(): void
    {
        $this->mediaQueryService
            ->expects(self::once())
            ->method('findByContentElementUid')
            ->willReturn([
                ['uid' => 5, 'url' => '/file.pdf', 'mime_type' => 'application/pdf', 'title' => '', 'alternative' => '', 'width' => null, 'height' => null],
            ]);

        $result = $this->normalizer->normalize(['uid' => 20, 'imagecols' => 1]);

        self::assertCount(1, $result->media);
        self::assertNull($result->media[0]->width);
        self::assertNull($result->media[0]->height);
    }

    #[Test]
    public function usesDefaultValuesWhenFieldsMissing(): void
    {
        $this->mediaQueryService
            ->method('findByContentElementUid')
            ->willReturn([]);

        $result = $this->normalizer->normalize(['uid' => 5]);

        self::assertSame(5, $result->id);
        self::assertSame('textmedia', $result->type);
        self::assertSame('', $result->headline);
        self::assertSame('', $result->properties['bodytext']);
        self::assertSame(0, $result->properties['mediaPosition']);
        self::assertSame(1, $result->properties['mediaColumns']);
        self::assertSame([], $result->media);
    }

    #[Test]
    public function queriesAssetsFieldNotImageField(): void
    {
        // Verify that 'assets' (not 'image') is used as the fieldName for textmedia
        $this->mediaQueryService
            ->expects(self::once())
            ->method('findByContentElementUid')
            ->with(self::anything(), 'assets')
            ->willReturn([]);

        $this->normalizer->normalize(['uid' => 1]);
    }

    #[Test]
    public function normalizeUidAsStringFromDatabaseRowCastsToInt(): void
    {
        // TYPO3 database rows often arrive with all values as strings.
        // The normalizer must cast uid to int before passing it to the query service
        // and before writing it into the DTO.
        $this->mediaQueryService
            ->expects(self::once())
            ->method('findByContentElementUid')
            ->with(42, 'assets')
            ->willReturn([]);

        $result = $this->normalizer->normalize([
            'uid' => '42',
            'header' => 'String uid row',
            'bodytext' => '',
            'imageorient' => '8',
            'imagecols' => '4',
        ]);

        self::assertSame(42, $result->id);
        self::assertSame(8, $result->properties['mediaPosition']);
        self::assertSame(4, $result->properties['mediaColumns']);
    }

    /**
     * @return array<string, array{int}>
     */
    public static function imageorientProvider(): array
    {
        return [
            'above center (0)'      => [0],
            'above right (1)'       => [1],
            'above left (2)'        => [2],
            'below center (8)'      => [8],
            'below right (9)'       => [9],
            'below left (10)'       => [10],
            'in text right (17)'    => [17],
            'in text left (18)'     => [18],
            'beside text right (25)' => [25],
            'beside text left (26)' => [26],
        ];
    }

    #[Test]
    #[DataProvider('imageorientProvider')]
    public function normalizeImageorientValueIsPassedThroughAsMediaPosition(int $imageorient): void
    {
        // All documented TYPO3 imageorient values must be forwarded verbatim as
        // mediaPosition — the normalizer must not remap or filter them.
        $this->mediaQueryService
            ->method('findByContentElementUid')
            ->willReturn([]);

        $result = $this->normalizer->normalize([
            'uid'         => 1,
            'imageorient' => $imageorient,
            'imagecols'   => 1,
        ]);

        self::assertSame($imageorient, $result->properties['mediaPosition']);
    }

    #[Test]
    public function normalizeImagecolsZeroReturnsZeroMediaColumns(): void
    {
        // imagecols=0 is an unusual but valid database value; the int cast must
        // not silently replace it with the default of 1.
        $this->mediaQueryService
            ->method('findByContentElementUid')
            ->willReturn([]);

        $result = $this->normalizer->normalize([
            'uid'       => 1,
            'imagecols' => 0,
        ]);

        self::assertSame(0, $result->properties['mediaColumns']);
    }

    #[Test]
    public function normalizeMissingUidQueriesWithUidZero(): void
    {
        // When the uid key is absent entirely the normalizer falls back to 0.
        // The query service must receive 0, not a missing argument.
        $this->mediaQueryService
            ->expects(self::once())
            ->method('findByContentElementUid')
            ->with(0, 'assets')
            ->willReturn([]);

        $result = $this->normalizer->normalize([]);

        self::assertSame(0, $result->id);
    }
}
