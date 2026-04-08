<?php

declare(strict_types=1);

namespace DMF\ContentApi\Tests\Api;

use PHPUnit\Framework\Attributes\Test;

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
 *   /api-test-media  — visible page with a textmedia content element
 *   /api-test-hidden — hidden page (must return 404)
 */
final class PageEndpointTest extends ApiTestCase
{
    /**
     * Slug of a visible fixture page that always has at least two content elements.
     */
    private const FIXTURE_SLUG_HOME = 'api-test-home';

    /**
     * Slug of a visible fixture page that has a textmedia content element.
     */
    private const FIXTURE_SLUG_MEDIA = 'api-test-media';

    /**
     * Slug of a hidden fixture page — must never be returned as HTTP 200.
     */
    private const FIXTURE_SLUG_HIDDEN = 'api-test-hidden';

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
     */
    private function skipUnlessFixturesLoaded(): void
    {
        $response = $this->request('GET', '/api/v1/pages/' . self::FIXTURE_SLUG_HOME);
        if ($response->getStatusCode() !== 200) {
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

        $this->assertJsonStructure($body['page'], ['id', 'slug', 'title', 'content']);
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
    // Language routing (informational — skipped when not configured)
    // -------------------------------------------------------------------------

    #[Test]
    public function germanLanguagePrefixReturnsPageOrSkips(): void
    {
        // The German language variant uses base=/de/, so the URL is /de/api-test-home.
        // This test verifies that language routing is wired up; if the DE language
        // is not reachable it marks the test as skipped rather than failing, because
        // language routing depends on TYPO3 site config and server rewrite rules that
        // may differ between v12 and v13 installations.
        $response = $this->request('GET', '/de/' . self::FIXTURE_SLUG_HOME);
        $status = $response->getStatusCode();

        if ($status === 404 || $status === 500) {
            self::markTestSkipped(
                \sprintf(
                    'German language routing returned HTTP %d for /de/%s. '
                    . 'Verify that the api-test-site config is deployed and TYPO3 '
                    . 'language routing is configured correctly.',
                    $status,
                    self::FIXTURE_SLUG_HOME,
                ),
            );
        }

        self::assertSame(200, $status, 'German language URL must return HTTP 200.');

        $body = $response->toArray(false);
        self::assertSame('de', $body['meta']['language'] ?? '', 'meta.language must be "de" for German URL.');
    }
}
