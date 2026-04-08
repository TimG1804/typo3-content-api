<?php

declare(strict_types=1);

namespace DMF\ContentApi\Dto;

final readonly class AccessDto
{
    /**
     * @param int[] $feGroups Frontend user group IDs required to access this page.
     *                        Empty array means public access.
     *                        [-1] means any authenticated user.
     */
    public function __construct(
        public array $feGroups,
        public ?int $starttime,
        public ?int $endtime,
        public bool $extendToSubpages,
    ) {}
}
