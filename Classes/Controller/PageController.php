<?php

declare(strict_types=1);

namespace DMF\ContentApi\Controller;

use DMF\ContentApi\Dto\ErrorDto;
use DMF\ContentApi\Dto\MetaDto;
use DMF\ContentApi\Dto\PageResponseDto;
use DMF\ContentApi\Normalizer\PageNormalizer;
use DMF\ContentApi\Query\ContentQueryServiceInterface;
use DMF\ContentApi\Query\PageQueryServiceInterface;
use DMF\ContentApi\Serializer\SerializerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;

final class PageController
{
    private const API_VERSION = '1.0';

    public function __construct(
        private readonly PageQueryServiceInterface $pageQueryService,
        private readonly ContentQueryServiceInterface $contentQueryService,
        private readonly PageNormalizer $pageNormalizer,
        private readonly SerializerInterface $serializer,
    ) {}

    public function show(ServerRequestInterface $request, string $slug): ResponseInterface
    {
        /** @var SiteInterface $site */
        $site = $request->getAttribute('site');
        $language = $request->getAttribute('language');
        $siteIdentifier = $site->getIdentifier();

        $pageData = $this->pageQueryService->findBySlug($slug, $siteIdentifier);
        if ($pageData === null) {
            return $this->errorResponse(404, 'Page not found', 'No page found for slug "' . $slug . '".');
        }

        // Pass the language uid so the content query layer can scope overlay application correctly.
        $languageUid = $language?->getLanguageId() ?? 0;
        $contentElements = $this->contentQueryService->findByPageUid((int) $pageData['uid'], $languageUid);

        $pageDto = $this->pageNormalizer->normalize($pageData, $contentElements);

        $meta = new MetaDto(
            apiVersion: self::API_VERSION,
            language: $this->resolveLanguageCode($language),
            site: $siteIdentifier,
        );

        $response = new PageResponseDto(meta: $meta, page: $pageDto);

        return new JsonResponse(
            json_decode($this->serializer->serialize($response), true),
            200,
            ['Content-Type' => 'application/json'],
        );
    }

    /**
     * Resolves the two-letter ISO language code from a SiteLanguage instance.
     *
     * getTwoLetterIsoCode() was deprecated in TYPO3 v12.1 and removed in v13.
     * In v13, the equivalent is getLocale()->getLanguageCode().
     *
     * @param mixed $language SiteLanguage|null
     */
    private function resolveLanguageCode(mixed $language): string
    {
        if ($language === null) {
            return 'en';
        }
        // TYPO3 v13+: getLocale() returns a Locale object with getLanguageCode()
        // TYPO3 v12:  getTwoLetterIsoCode() is the equivalent method
        if (method_exists($language, 'getTwoLetterIsoCode')) {
            return $language->getTwoLetterIsoCode();
        }

        return $language->getLocale()->getLanguageCode();
    }

    private function errorResponse(int $status, string $error, string $message): ResponseInterface
    {
        $errorDto = new ErrorDto(status: $status, error: $error, message: $message);

        return new JsonResponse(
            json_decode($this->serializer->serialize($errorDto), true),
            $status,
            ['Content-Type' => 'application/json'],
        );
    }
}
