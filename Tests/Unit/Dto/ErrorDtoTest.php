<?php

declare(strict_types=1);

namespace DMF\ContentApi\Tests\Unit\Dto;

use DMF\ContentApi\Dto\ErrorDto;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ErrorDtoTest extends TestCase
{
    #[Test]
    public function constructorSetsAllProperties(): void
    {
        $dto = new ErrorDto(status: 404, error: 'Not Found', message: 'Page not found');

        self::assertSame(404, $dto->status);
        self::assertSame('Not Found', $dto->error);
        self::assertSame('Page not found', $dto->message);
    }
}
