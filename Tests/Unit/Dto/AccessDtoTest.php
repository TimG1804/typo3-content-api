<?php

declare(strict_types=1);

namespace DMF\ContentApi\Tests\Unit\Dto;

use DMF\ContentApi\Dto\AccessDto;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AccessDtoTest extends TestCase
{
    #[Test]
    public function constructorSetsAllProperties(): void
    {
        $dto = new AccessDto(
            feGroups: [1, 2, 3],
            starttime: 1700000000,
            endtime: 1800000000,
            extendToSubpages: true,
        );

        self::assertSame([1, 2, 3], $dto->feGroups);
        self::assertSame(1700000000, $dto->starttime);
        self::assertSame(1800000000, $dto->endtime);
        self::assertTrue($dto->extendToSubpages);
    }

    #[Test]
    public function publicAccessHasEmptyFeGroupsAndNullTimes(): void
    {
        $dto = new AccessDto(
            feGroups: [],
            starttime: null,
            endtime: null,
            extendToSubpages: false,
        );

        self::assertSame([], $dto->feGroups);
        self::assertNull($dto->starttime);
        self::assertNull($dto->endtime);
        self::assertFalse($dto->extendToSubpages);
    }
}
