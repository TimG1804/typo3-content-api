<?php

declare(strict_types=1);

namespace DMF\ContentApi\Dto;

final readonly class PageResponseDto
{
    public function __construct(
        public MetaDto $meta,
        public PageDto $page,
    ) {}
}
