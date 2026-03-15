<?php

declare(strict_types=1);

namespace DMF\ContentApi\Dto;

final readonly class MetaDto
{
    public function __construct(
        public string $apiVersion,
        public string $language,
        public string $site,
    ) {}
}
