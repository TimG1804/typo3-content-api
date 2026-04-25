<?php

declare(strict_types=1);

namespace DMF\ContentApi\Tests\Api;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * API contract tests for GET /api/v1/pages/{slug}.
 *
 * These tests send real HTTP requests against a running DDEV TYPO3 instance.
 * They require the API test fixtures to be loaded first:
 *
 *   ddev setup-api-fixtures v13   # or v12
 *
 * Target URL is read from CONTENT_API_BASE_URL (default: https://v13.content-api.ddev.site).
 * Tests are skipped automatically when the DDEV instance is not reachable.
 *
 * Fixed fixture slugs used by this test class:
 *   /api-test-home   — visible page with text + textmedia content elements
 *   /api-test-media  — visible page with a textmedia content element, no_index=1
 *   /api-test-hidden — hidden page (must return 404)
 */
final class PageEndpointTest extends ApiTestCase
{
    /**
     * Slug of a visible fixture page that always has at least two content elements.
     */
    private const FIXTURE_SLUG_HOME = 'api-test-home';

    /**
     * Slug of a visible fixture page that has a textmedia content element and no_index=1.
     */
    private const FIXTURE_SLUG_MEDIA = 'api-test-media';

    /**
     * Slug of a hidden fixture page — must never be returned as HTTP 200.
     */
    private const FIXTURE_SLUG_HIDDEN = 'api-test-hidden';

    private static ?bool $fixturesLoaded = null;

    protected function setUp(): void
    {
        // Calls skipUnlessInstanceReachable() internally.
        parent::setUp();
        $this->skipUnlessFixturesLoaded();
    }

    // -------------------------------------------------------------------------
    // Fixture guard
    // -------------------------------------------------------------------------

    /**
     * Verifies that the API test fixtures are loaded by checking whether the
     * fixture home page is reachable. Skips the test with a clear message when
     * the fixtures are missing, so engineers know exactly what to run.
     *
     * The result is cached statically so the probe runs only once per process,
     * not once per test method.
     */
    private function skipUnlessFixturesLoaded(): void
    {
        if (self::$fixturesLoaded !== null) {
            if (!self::$fixturesLoaded) {
                self::markTestSkipped(
                    'API test fixtures not loaded. '
                    . 'Run "ddev setup-api-fixtures" first, then re-run the API tests.',
                );
            }

            return;
        }

        $response = $this->request('GET', '/api/v1/pages/' . self::FIXTURE_SLUG_HOME);
        self::$fixturesLoaded = $response->getStatusCode() === 200;

        if (!self::$fixturesLoaded) {
            self::markTestSkipped(
                'API test fixtures not loaded. '
                . 'Run "ddev setup-api-fixtures" first, then re-run the API tests.',
            );
        }
    }

    // -------------------------------------------------------------------------
    // Status code + Content-Type contract
    // -------------------------------------------------------------------------

    #[Test]
    public function fixtureHomePageReturns200AndJsonContentType(): void
    {
        $response = $this->request('GET', '/api/v1/pages/' . self::FIXTURE_SLUG_HOME);

        self::assertSame(200, $response->getStatusCode());

        $contentType = $response->getHeaders(false)['content-type'][0] ?? '';
        self::assertStringContainsString(
            'application/json',
            $contentType,
            'Content-Type header must contain application/json.',
        );
    }

    #[Test]
    public function fixtureMediaPageReturns200(): void
    {
        $response = $this->request('GET', '/api/v1/pages/' . self::FIXTURE_SLUG_MEDIA);

        self::assertSame(200, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Response body structure (top-level envelope)
    // -------------------------------------------------------------------------

    #[Test]
    public function pageEndpointResponseContainsMetaAndPageKeys(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);

        $this->assertJsonStructure($body, ['meta', 'page']);
    }

    // -------------------------------------------------------------------------
    // Page DTO structure
    // -------------------------------------------------------------------------

    #[Test]
    public function pageResponseContainsRequiredPageFields(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);

        $this->assertJsonStructure($body['page'], [
            'id', 'slug', 'title', 'navTitle', 'description',
            'doktype', 'updatedAt', 'seo', 'access', 'content',
        ]);
    }

    #[Test]
    public function pageIdIsPositiveInteger(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);
        $page = $body['page'];

        self::assertIsInt($page['id'], 'page.id must be an integer.');
        self::assertGreaterThan(0, $page['id'], 'page.id must be a positive integer.');
    }

    #[Test]
    public function pageSlugIsNonEmptyString(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);
        $page = $body['page'];

        self::assertIsString($page['slug'], 'page.slug must be a string.');
        self::assertNotEmpty($page['slug'], 'page.slug must not be empty.');
    }

    #[Test]
    public function pageTitleMatchesFixtureTitle(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);
        $page = $body['page'];

        self::assertIsString($page['title'], 'page.title must be a string.');
        self::assertSame('API Test Home', $page['title'], 'page.title must match fixture value.');
    }

    #[Test]
    public function pageContentIsArray(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);

        self::assertIsArray($body['page']['content'], 'page.content must be an array.');
    }

    // -------------------------------------------------------------------------
    // PageDto extended fields (navTitle, description, doktype, updatedAt)
    // -------------------------------------------------------------------------

    #[Test]
    public function pageNavTitleMatchesFixture(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);

        self::assertIsString($body['page']['navTitle'], 'page.navTitle must be a string.');
        self::assertSame('Home Nav', $body['page']['navTitle'], 'page.navTitle must match fixture value.');
    }

    #[Test]
    public function pageDescriptionMatchesFixture(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);

        self::assertIsString($body['page']['description'], 'page.description must be a string.');
        self::assertSame(
            'Meta description for API Test Home',
            $body['page']['description'],
            'page.description must match fixture value.',
        );
    }

    #[Test]
    public function pageDokypeIsInteger(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);

        self::assertIsInt($body['page']['doktype'], 'page.doktype must be an integer.');
        self::assertSame(1, $body['page']['doktype'], 'Fixture page doktype must be 1 (Standard).');
    }

    #[Test]
    public function pageUpdatedAtIsInteger(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);

        self::assertIsInt($body['page']['updatedAt'], 'page.updatedAt must be an integer.');
    }

    // -------------------------------------------------------------------------
    // SeoDto contract
    // -------------------------------------------------------------------------

    #[Test]
    public function pageSeoContainsRequiredFields(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);
        $seo = $body['page']['seo'];

        if ($seo === null) {
            self::markTestSkipped('page.seo is null — EXT:seo is not installed in this TYPO3 instance.');
        }

        $this->assertJsonStructure($seo, ['title', 'robots', 'canonicalUrl', 'ogTitle', 'ogDescription']);
    }

    #[Test]
    public function pageSeoTitleMatchesFixture(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);
        $seo = $body['page']['seo'];

        if ($seo === null) {
            self::markTestSkipped('page.seo is null — EXT:seo is not installed in this TYPO3 instance.');
        }

        self::assertSame(
            'API Test Home – SEO Title',
            $seo['title'],
            'seo.title must match the seo_title fixture value.',
        );
    }

    #[Test]
    public function pageSeoRobotsIsIndexFollowForHomePage(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);
        $seo = $body['page']['seo'];

        if ($seo === null) {
            self::markTestSkipped('page.seo is null — EXT:seo is not installed in this TYPO3 instance.');
        }

        self::assertSame(
            'index,follow',
            $seo['robots'],
            'seo.robots must be "index,follow" for a page with no_index=0 and no_follow=0.',
        );
    }

    #[Test]
    public function pageSeoRobotsIsNoindexFollowForMediaPage(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_MEDIA);
        $seo = $body['page']['seo'];

        if ($seo === null) {
            self::markTestSkipped('page.seo is null — EXT:seo is not installed in this TYPO3 instance.');
        }

        self::assertSame(
            'noindex,follow',
            $seo['robots'],
            'seo.robots must be "noindex,follow" for a page with no_index=1.',
        );
    }

    #[Test]
    public function pageSeoOgTitleMatchesFixture(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);
        $seo = $body['page']['seo'];

        if ($seo === null) {
            self::markTestSkipped('page.seo is null — EXT:seo is not installed in this TYPO3 instance.');
        }

        self::assertSame('OG Title for Home', $seo['ogTitle'], 'seo.ogTitle must match fixture value.');
    }

    #[Test]
    public function pageSeoCanonicalUrlIsNullWhenNotSet(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);
        $seo = $body['page']['seo'];

        if ($seo === null) {
            self::markTestSkipped('page.seo is null — EXT:seo is not installed in this TYPO3 instance.');
        }

        self::assertNull($seo['canonicalUrl'], 'seo.canonicalUrl must be null when canonical_link is empty.');
    }

    // -------------------------------------------------------------------------
    // AccessDto contract
    // -------------------------------------------------------------------------

    #[Test]
    public function pageAccessContainsRequiredFields(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);

        $this->assertJsonStructure(
            $body['page']['access'],
            ['feGroups', 'starttime', 'endtime', 'extendToSubpages'],
        );
    }

    #[Test]
    public function pageAccessFeGroupsIsEmptyArrayForPublicPage(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);

        self::assertSame(
            [],
            $body['page']['access']['feGroups'],
            'access.feGroups must be an empty array for a page without frontend group restriction.',
        );
    }

    #[Test]
    public function pageAccessStarttimeIsNullWhenNotSet(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);

        self::assertNull(
            $body['page']['access']['starttime'],
            'access.starttime must be null when not configured.',
        );
    }

    #[Test]
    public function pageAccessEndtimeIsNullWhenNotSet(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);

        self::assertNull(
            $body['page']['access']['endtime'],
            'access.endtime must be null when not configured.',
        );
    }

    #[Test]
    public function pageAccessExtendToSubpagesIsFalseByDefault(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);

        self::assertFalse(
            $body['page']['access']['extendToSubpages'],
            'access.extendToSubpages must be false when not set.',
        );
    }

    // -------------------------------------------------------------------------
    // Content element structure
    // -------------------------------------------------------------------------

    #[Test]
    public function fixtureHomePageHasAtLeastTwoContentElements(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);
        $content = $body['page']['content'];

        self::assertGreaterThanOrEqual(
            2,
            \count($content),
            'Fixture home page must have at least 2 content elements (text + textmedia).',
        );
    }

    #[Test]
    public function eachContentElementHasRequiredFields(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);
        $content = $body['page']['content'];

        foreach ($content as $index => $element) {
            $this->assertJsonStructure(
                $element,
                ['id', 'type', 'headline', 'properties', 'media'],
            );
            self::assertIsInt(
                $element['id'],
                \sprintf('content[%d].id must be an integer.', $index),
            );
            self::assertIsString(
                $element['type'],
                \sprintf('content[%d].type must be a string.', $index),
            );
            self::assertIsArray(
                $element['properties'],
                \sprintf('content[%d].properties must be an array.', $index),
            );
            self::assertIsArray(
                $element['media'],
                \sprintf('content[%d].media must be an array.', $index),
            );
        }
    }

    #[Test]
    public function fixtureHomePageContainsTextContentElement(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);
        $types = array_column($body['page']['content'], 'type');

        self::assertContains('text', $types, 'Fixture home page must contain a "text" content element.');
    }

    #[Test]
    public function fixtureHomePageContainsTextmediaContentElement(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);
        $types = array_column($body['page']['content'], 'type');

        self::assertContains('textmedia', $types, 'Fixture home page must contain a "textmedia" content element.');
    }

    #[Test]
    public function fixtureMediaPageContainsTextmediaContentElement(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_MEDIA);
        $types = array_column($body['page']['content'], 'type');

        self::assertContains('textmedia', $types, 'Fixture media page must contain a "textmedia" content element.');
    }

    #[Test]
    public function textContentElementHeadlineMatchesFixture(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);

        $textElement = null;
        foreach ($body['page']['content'] as $element) {
            if ($element['type'] === 'text') {
                $textElement = $element;
                break;
            }
        }

        self::assertNotNull($textElement, 'A "text" content element must be present.');
        self::assertSame(
            'Test Headline',
            $textElement['headline'],
            'text element headline must match fixture value.',
        );
    }

    #[Test]
    public function textmediaContentElementHeadlineMatchesFixture(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);

        $textmediaElement = null;
        foreach ($body['page']['content'] as $element) {
            if ($element['type'] === 'textmedia') {
                $textmediaElement = $element;
                break;
            }
        }

        self::assertNotNull($textmediaElement, 'A "textmedia" content element must be present.');
        self::assertSame(
            'Textmedia Headline',
            $textmediaElement['headline'],
            'textmedia element headline must match fixture value.',
        );
    }

    // -------------------------------------------------------------------------
    // Hidden page — must return 404
    // -------------------------------------------------------------------------

    #[Test]
    public function hiddenFixturePageReturns404(): void
    {
        $response = $this->request('GET', '/api/v1/pages/' . self::FIXTURE_SLUG_HIDDEN);

        self::assertSame(
            404,
            $response->getStatusCode(),
            'Hidden pages must not be exposed by the API.',
        );
    }

    // -------------------------------------------------------------------------
    // 404 error contract (unknown slug)
    // -------------------------------------------------------------------------

    #[Test]
    public function unknownSlugReturns404(): void
    {
        $response = $this->request('GET', '/api/v1/pages/this-slug-does-absolutely-not-exist-xyzzy');

        self::assertSame(404, $response->getStatusCode());
    }

    #[Test]
    public function notFoundResponseBodyContainsErrorField(): void
    {
        $response = $this->request('GET', '/api/v1/pages/nonexistent-fixture-slug-xyzzy');
        $body = $response->toArray(false);

        self::assertArrayHasKey('error', $body, '404 response must contain an "error" field.');
        self::assertIsString($body['error'], '"error" field must be a string.');
        self::assertNotEmpty($body['error'], '"error" field must not be empty.');
    }

    #[Test]
    public function notFoundResponseHasJsonContentType(): void
    {
        $response = $this->request('GET', '/api/v1/pages/nonexistent-fixture-slug-xyzzy');

        $contentType = $response->getHeaders(false)['content-type'][0] ?? '';
        self::assertStringContainsString(
            'application/json',
            $contentType,
            '404 response Content-Type header must contain application/json.',
        );
    }

    // -------------------------------------------------------------------------
    // Meta envelope structure
    // -------------------------------------------------------------------------

    #[Test]
    public function metaContainsApiVersionAndLanguageAndSite(): void
    {
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);
        $meta = $body['meta'] ?? [];

        $this->assertJsonStructure($meta, ['apiVersion', 'language', 'site']);
        self::assertIsString($meta['apiVersion'], 'meta.apiVersion must be a string.');
        self::assertIsString($meta['language'], 'meta.language must be a string.');
        self::assertIsString($meta['site'], 'meta.site must be a string.');
    }

    #[Test]
    public function metaSiteIsNonEmptyString(): void
    {
        // Fixture pages live under the existing site root (pid=1), so the site
        // identifier is whatever the installed TYPO3 instance uses (e.g. "main").
        // We only assert it is a non-empty string — the exact value is
        // installation-specific and must not be hard-coded here.
        $body = $this->getJson('/api/v1/pages/' . self::FIXTURE_SLUG_HOME);

        self::assertIsString($body['meta']['site'], 'meta.site must be a string.');
        self::assertNotEmpty($body['meta']['site'], 'meta.site must not be empty.');
    }

    // -------------------------------------------------------------------------
    // Language routing (placeholder — skipped until implemented)
    // -------------------------------------------------------------------------

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    #[Test]
    public function germanLanguagePrefixReturnsPageOrSkips(): void
    {
        // NOTE: The correct URL pattern for language support in this API will be
        // determined once multi-language routing is implemented (see ROADMAP Phase 1).
        // The likely approach is Accept-Language header or a language query parameter,
        // NOT a URL prefix like /de/api/v1/pages/{slug}.
        //
        // This test currently probes /de/api-test-home (TYPO3 frontend URL) and
        // skips whenever it does not get a JSON API response. Once language routing
        // is implemented, the URL and assertion should be updated accordingly.
        $response = $this->request('GET', '/de/' . self::FIXTURE_SLUG_HOME);
        $status = $response->getStatusCode();

        if ($status === 404 || $status === 500) {
            self::markTestSkipped(
                \sprintf(
                    'German language routing returned HTTP %d for /de/%s. '
                    . 'Language-prefixed API routing is not yet implemented.',
                    $status,
                    self::FIXTURE_SLUG_HOME,
                ),
            );
        }

        // If the response is not JSON, TYPO3 frontend handled the URL (HTML response).
        $contentType = $response->getHeaders(false)['content-type'][0] ?? '';
        if (!str_contains($contentType, 'application/json')) {
            self::markTestSkipped(
                'German URL /de/api-test-home returned a non-JSON response. '
                . 'Language-prefixed API routing is not yet implemented.',
            );
        }

        self::assertSame(200, $status, 'German language URL must return HTTP 200.');

        $body = $response->toArray(false);
        self::assertSame('de', $body['meta']['language'] ?? '', 'meta.language must be "de" for German URL.');
    }
}
