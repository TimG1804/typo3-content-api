<?php

declare(strict_types=1);

namespace DMF\ContentApi\Tests\Functional\Query;

use DMF\ContentApi\Query\ContentQueryService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversClass(ContentQueryService::class)]
final class ContentQueryServiceTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        '3m5/typo3-content-api',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/tt_content.csv');
    }

    #[Test]
    public function findByPageUidReturnsContentElementsForPage(): void
    {
        $subject = $this->get(ContentQueryService::class);

        // Fixture: page 2 has uid=1 (visible) and uid=2 (visible), uid=3 (hidden), uid=4 (translation)
        $result = $subject->findByPageUid(2);

        // Only the two visible default-language records should be returned
        self::assertCount(2, $result);
    }

    #[Test]
    public function findByPageUidReturnsEmptyArrayForPageWithNoContent(): void
    {
        $subject = $this->get(ContentQueryService::class);

        // Fixture: page 1 (root) has no tt_content records
        $result = $subject->findByPageUid(1);

        self::assertSame([], $result);
    }

    #[Test]
    public function findByPageUidDoesNotReturnHiddenContentElements(): void
    {
        $subject = $this->get(ContentQueryService::class);

        // Fixture: uid=3 on page 2 has hidden=1
        $result = $subject->findByPageUid(2);

        $uids = array_map('intval', array_column($result, 'uid'));
        self::assertNotContains(3, $uids);
    }

    #[Test]
    public function findByPageUidReturnsOnlyDefaultLanguageRecords(): void
    {
        $subject = $this->get(ContentQueryService::class);

        // Fixture: uid=4 on page 2 has sys_language_uid=1 and l18n_parent=1 (translation row)
        $result = $subject->findByPageUid(2);

        foreach ($result as $row) {
            self::assertSame(0, (int)$row['sys_language_uid'], 'Only sys_language_uid=0 records expected');
        }
    }

    #[Test]
    public function findByPageUidReturnsRecordsOrderedByColPosThenSorting(): void
    {
        $subject = $this->get(ContentQueryService::class);

        // Fixture page 2: uid=1 colPos=0 sorting=256, uid=2 colPos=1 sorting=256
        // Expected order: uid=1 first (colPos=0), then uid=2 (colPos=1)
        $result = $subject->findByPageUid(2);

        self::assertCount(2, $result);
        self::assertSame(0, (int)$result[0]['colPos']);
        self::assertSame(1, (int)$result[1]['colPos']);
    }
}
