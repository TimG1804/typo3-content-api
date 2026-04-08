<?php

declare(strict_types=1);

namespace DMF\ContentApi\Dto;

final readonly class SeoDto
{
    public function __construct(
        public string $title,
        public string $robots,
        public ?string $canonicalUrl,
        public string $ogTitle,
        public string $ogDescription,
    ) {}
}
