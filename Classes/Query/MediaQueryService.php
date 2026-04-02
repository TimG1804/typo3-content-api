<?php

declare(strict_types=1);

namespace DMF\ContentApi\Query;

use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;

final readonly class MediaQueryService implements MediaQueryServiceInterface
{
    public function __construct(
        private FileRepository $fileRepository,
        private ResourceFactory $resourceFactory,
    ) {}

    public function findByUid(int $uid): ?array
    {
        try {
            $reference = $this->resourceFactory->getFileReferenceObject($uid);

            return $this->extractData($reference);
        } catch (ResourceDoesNotExistException|\InvalidArgumentException) {
            return null;
        }
    }

    public function findByContentElementUid(int $contentElementUid, string $fieldName): array
    {
        $references = $this->fileRepository->findByRelation('tt_content', $fieldName, $contentElementUid);

        return array_map(
            fn(FileReference $reference): array => $this->extractData($reference),
            $references,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function extractData(FileReference $reference): array
    {
        $width = $reference->getProperty('width');
        $height = $reference->getProperty('height');

        return [
            'uid' => $reference->getUid(),
            'url' => $reference->getPublicUrl() ?? '',
            'mime_type' => $reference->getMimeType(),
            'title' => $reference->getTitle() ?? '',
            'alternative' => $reference->getAlternative() ?? '',
            'width' => $width !== null && $width !== '' ? (int) $width : null,
            'height' => $height !== null && $height !== '' ? (int) $height : null,
        ];
    }
}
