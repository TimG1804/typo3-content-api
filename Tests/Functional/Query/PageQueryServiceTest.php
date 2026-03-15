<?php

declare(strict_types=1);

namespace DMF\ContentApi\Tests\Functional\Query;

use DMF\ContentApi\Query\PageQueryService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversClass(PageQueryService::class)]
final class PageQueryServiceTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        '3m5/typo3-content-api',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages.csv');
        $this->writeSiteConfigurationYaml();
    }

    #[Test]
    public function findBySlugReturnsPageForKnownSlug(): void
    {
        $subject = $this->get(PageQueryService::class);

        $result = $subject->findBySlug('/about', 'test-site');

        self::assertNotNull($result);
        self::assertIsArray($result);
        self::assertSame(2, (int)$result['uid']);
        self::assertSame('/about', $result['slug']);
        self::assertSame('About', $result['title']);
    }

    #[Test]
    public function findBySlugReturnsNullForUnknownSlug(): void
    {
        $subject = $this->get(PageQueryService::class);

        $result = $subject->findBySlug('/does-not-exist', 'test-site');

        self::assertNull($result);
    }

    #[Test]
    public function findBySlugDoesNotReturnHiddenPage(): void
    {
        $subject = $this->get(PageQueryService::class);

        // uid=3 exists with slug=/contact but is hidden=1
        $result = $subject->findBySlug('/contact', 'test-site');

        self::assertNull($result);
    }

    #[Test]
    public function findByUidReturnsPageForKnownUid(): void
    {
        $subject = $this->get(PageQueryService::class);

        $result = $subject->findByUid(2);

        self::assertNotNull($result);
        self::assertIsArray($result);
        self::assertSame(2, (int)$result['uid']);
        self::assertSame('About', $result['title']);
    }

    #[Test]
    public function findByUidReturnsNullForNonExistentUid(): void
    {
        $subject = $this->get(PageQueryService::class);

        $result = $subject->findByUid(99999);

        self::assertNull($result);
    }

    /**
     * Writes a minimal TYPO3 site configuration so SiteFinder can resolve
     * the 'test-site' identifier used in PageQueryService slug lookups.
     */
    private function writeSiteConfigurationYaml(): void
    {
        $configPath = $this->instancePath . '/typo3conf/sites/test-site';
        if (!is_dir($configPath)) {
            mkdir($configPath, 0777, true);
        }

        file_put_contents(
            $configPath . '/config.yaml',
            implode("\n", [
                'rootPageId: 1',
                'base: http://localhost/',
                'languages:',
                '  - languageId: 0',
                '    title: English',
                '    locale: en_US.UTF-8',
                '    base: /',
                '    flag: us',
            ]),
        );

        // Force SiteConfiguration to re-scan the filesystem, bypassing both the
        // runtime cache and the core PHP cache, so SiteFinder picks up the
        // newly written YAML file within the same container lifecycle.
        $this->get(SiteConfiguration::class)->getAllExistingSites(false);
    }
}
