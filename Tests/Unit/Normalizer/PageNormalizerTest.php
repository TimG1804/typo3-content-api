<?php

declare(strict_types=1);

namespace DMF\ContentApi\Tests\Unit\Normalizer;

use DMF\ContentApi\Normalizer\ContentElementNormalizerRegistry;
use DMF\ContentApi\Normalizer\PageNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class PageNormalizerTest extends TestCase
{
    private PageNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new PageNormalizer(
            new ContentElementNormalizerRegistry([], new NullLogger()),
        );
    }

    #[Test]
    public function normalizesPageWithContentElements(): void
    {
        $pageData = [
            'uid' => 1,
            'slug' => '/home',
            'title' => 'Homepage',
            'nav_title' => 'Home',
            'description' => 'Welcome page',
            'doktype' => 1,
            'tstamp' => 1700000000,
            'lastUpdated' => 0,
            'fe_group' => '',
            'starttime' => 0,
            'endtime' => 0,
            'extendToSubpages' => 0,
        ];

        $contentElements = [
            ['uid' => 10, 'CType' => 'text', 'header' => 'First'],
            ['uid' => 11, 'CType' => 'image', 'header' => 'Second'],
        ];

        $result = $this->normalizer->normalize($pageData, $contentElements);

        self::assertSame(1, $result->id);
        self::assertSame('/home', $result->slug);
        self::assertSame('Homepage', $result->title);
        self::assertSame('Home', $result->navTitle);
        self::assertSame('Welcome page', $result->description);
        self::assertSame(1, $result->doktype);
        self::assertCount(2, $result->content);
        self::assertSame('First', $result->content[0]->headline);
        self::assertSame('Second', $result->content[1]->headline);
    }

    #[Test]
    public function normalizesPageWithEmptyContent(): void
    {
        $result = $this->normalizer->normalize(
            ['uid' => 5, 'slug' => '/empty', 'title' => 'Empty', 'nav_title' => '', 'description' => '',
                'doktype' => 1, 'tstamp' => 0, 'lastUpdated' => 0, 'fe_group' => '',
                'starttime' => 0, 'endtime' => 0, 'extendToSubpages' => 0],
            [],
        );

        self::assertSame(5, $result->id);
        self::assertSame([], $result->content);
    }

    #[Test]
    public function updatedAtPrefersLastUpdatedOverTstamp(): void
    {
        $pageData = [
            'uid' => 1, 'slug' => '/', 'title' => 'T', 'nav_title' => '', 'description' => '',
            'doktype' => 1, 'tstamp' => 1000, 'lastUpdated' => 2000,
            'fe_group' => '', 'starttime' => 0, 'endtime' => 0, 'extendToSubpages' => 0,
        ];

        $result = $this->normalizer->normalize($pageData, []);

        self::assertSame(2000, $result->updatedAt);
    }

    #[Test]
    public function updatedAtFallsBackToTstampWhenLastUpdatedIsZero(): void
    {
        $pageData = [
            'uid' => 1, 'slug' => '/', 'title' => 'T', 'nav_title' => '', 'description' => '',
            'doktype' => 1, 'tstamp' => 1000, 'lastUpdated' => 0,
            'fe_group' => '', 'starttime' => 0, 'endtime' => 0, 'extendToSubpages' => 0,
        ];

        $result = $this->normalizer->normalize($pageData, []);

        self::assertSame(1000, $result->updatedAt);
    }

    #[Test]
    public function seoIsNullWhenExtSeoFieldsAbsent(): void
    {
        $pageData = [
            'uid' => 1, 'slug' => '/', 'title' => 'T', 'nav_title' => '', 'description' => '',
            'doktype' => 1, 'tstamp' => 0, 'lastUpdated' => 0,
            'fe_group' => '', 'starttime' => 0, 'endtime' => 0, 'extendToSubpages' => 0,
        ];

        $result = $this->normalizer->normalize($pageData, []);

        self::assertNull($result->seo);
    }

    #[Test]
    public function seoTitleFallsBackToPageTitleWhenEmpty(): void
    {
        $pageData = [
            'uid' => 1, 'slug' => '/', 'title' => 'Page Title', 'nav_title' => '', 'description' => '',
            'doktype' => 1, 'tstamp' => 0, 'lastUpdated' => 0,
            'fe_group' => '', 'starttime' => 0, 'endtime' => 0, 'extendToSubpages' => 0,
            'seo_title' => '', 'no_index' => 0, 'no_follow' => 0,
            'canonical_link' => '', 'og_title' => '', 'og_description' => '',
        ];

        $result = $this->normalizer->normalize($pageData, []);

        self::assertNotNull($result->seo);
        self::assertSame('Page Title', $result->seo->title);
    }

    /**
     * @return array<string, array{int, int, string}>
     */
    public static function robotsProvider(): array
    {
        return [
            'index+follow'     => [0, 0, 'index,follow'],
            'noindex+follow'   => [1, 0, 'noindex,follow'],
            'index+nofollow'   => [0, 1, 'index,nofollow'],
            'noindex+nofollow' => [1, 1, 'noindex,nofollow'],
        ];
    }

    #[Test]
    #[DataProvider('robotsProvider')]
    public function robotsStringDerivedFromNoIndexAndNoFollow(int $noIndex, int $noFollow, string $expected): void
    {
        $pageData = [
            'uid' => 1, 'slug' => '/', 'title' => 'T', 'nav_title' => '', 'description' => '',
            'doktype' => 1, 'tstamp' => 0, 'lastUpdated' => 0,
            'fe_group' => '', 'starttime' => 0, 'endtime' => 0, 'extendToSubpages' => 0,
            'seo_title' => 'T', 'no_index' => $noIndex, 'no_follow' => $noFollow,
            'canonical_link' => '', 'og_title' => '', 'og_description' => '',
        ];

        $result = $this->normalizer->normalize($pageData, []);

        self::assertNotNull($result->seo);
        self::assertSame($expected, $result->seo->robots);
    }

    #[Test]
    public function accessFeGroupsParsedFromCommaSeparatedString(): void
    {
        $pageData = [
            'uid' => 1, 'slug' => '/', 'title' => 'T', 'nav_title' => '', 'description' => '',
            'doktype' => 1, 'tstamp' => 0, 'lastUpdated' => 0,
            'fe_group' => '1,2,3', 'starttime' => 0, 'endtime' => 0, 'extendToSubpages' => 0,
        ];

        $result = $this->normalizer->normalize($pageData, []);

        self::assertSame([1, 2, 3], $result->access->feGroups);
    }

    #[Test]
    public function accessStarttimeAndEndtimeAreNullWhenZero(): void
    {
        $pageData = [
            'uid' => 1, 'slug' => '/', 'title' => 'T', 'nav_title' => '', 'description' => '',
            'doktype' => 1, 'tstamp' => 0, 'lastUpdated' => 0,
            'fe_group' => '', 'starttime' => 0, 'endtime' => 0, 'extendToSubpages' => 0,
        ];

        $result = $this->normalizer->normalize($pageData, []);

        self::assertNull($result->access->starttime);
        self::assertNull($result->access->endtime);
    }

    #[Test]
    public function accessStarttimeAndEndtimeReturnTimestampWhenSet(): void
    {
        $pageData = [
            'uid' => 1, 'slug' => '/', 'title' => 'T', 'nav_title' => '', 'description' => '',
            'doktype' => 1, 'tstamp' => 0, 'lastUpdated' => 0,
            'fe_group' => '', 'starttime' => 1700000000, 'endtime' => 1800000000, 'extendToSubpages' => 1,
        ];

        $result = $this->normalizer->normalize($pageData, []);

        self::assertSame(1700000000, $result->access->starttime);
        self::assertSame(1800000000, $result->access->endtime);
        self::assertTrue($result->access->extendToSubpages);
    }
}
