<?php

declare(strict_types=1);

namespace DMF\ContentApi\Query;

interface MediaQueryServiceInterface
{
    /**
     * Find a file reference by its uid.
     *
     * @return array<string, mixed>|null Resolved file data or null if not found
     */
    public function findByUid(int $uid): ?array;

    /**
     * Find all file references for a tt_content record.
     *
     * Pass the exact tt_content field name as stored in sys_file_reference.fieldname,
     * e.g. 'assets' for textmedia, 'image' for the image CType.
     *
     * @return array<int, array<string, mixed>> Resolved file data arrays
     */
    public function findByContentElementUid(int $contentElementUid, string $fieldName): array;
}
