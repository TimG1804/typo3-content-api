<?php

declare(strict_types=1);

namespace DMF\ContentApi\Tests\Api;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Abstract base class for API/E2E tests that send real HTTP requests
 * against a running DDEV TYPO3 instance.
 *
 * Tests are skipped automatically when the DDEV instance is not reachable,
 * so this suite is safe to run in environments where DDEV is not started.
 *
 * The target base URL is read from the CONTENT_API_BASE_URL environment
 * variable and defaults to https://v13.content-api.ddev.site.
 */
abstract class ApiTestCase extends TestCase
{
    private const DEFAULT_BASE_URL = 'https://v13.content-api.ddev.site';

    /**
     * Connection timeout in seconds used for the reachability probe.
     */
    private const PROBE_TIMEOUT = 3.0;

    private static ?HttpClientInterface $httpClient = null;

    private static ?bool $instanceReachable = null;

    protected static function getBaseUrl(): string
    {
        return rtrim((string) (getenv('CONTENT_API_BASE_URL') ?: self::DEFAULT_BASE_URL), '/');
    }

    protected static function getHttpClient(): HttpClientInterface
    {
        if (self::$httpClient === null) {
            self::$httpClient = HttpClient::create([
                'verify_peer' => false,
                'verify_host' => false,
                'timeout' => 10.0,
            ]);
        }

        return self::$httpClient;
    }

    /**
     * Performs a GET request and returns the decoded JSON body as an array.
     *
     * @throws \RuntimeException when the response is not HTTP 200.
     *
     * @return array<string, mixed>
     */
    protected function getJson(string $path): array
    {
        $url = self::getBaseUrl() . '/' . ltrim($path, '/');
        $response = self::getHttpClient()->request('GET', $url);
        $statusCode = $response->getStatusCode();

        if ($statusCode !== 200) {
            throw new \RuntimeException(
                \sprintf('Expected HTTP 200 for GET %s, got %d.', $url, $statusCode),
            );
        }

        return $response->toArray(false);
    }

    /**
     * Performs a GET request and returns the raw response object, regardless
     * of status code. Use this when testing error cases.
     */
    protected function request(string $method, string $path): ResponseInterface
    {
        $url = self::getBaseUrl() . '/' . ltrim($path, '/');

        return self::getHttpClient()->request(strtoupper($method), $url);
    }

    /**
     * Asserts that every key in $requiredKeys is present in $data.
     *
     * @param array<string, mixed> $data
     * @param list<string>         $requiredKeys
     */
    protected function assertJsonStructure(array $data, array $requiredKeys): void
    {
        foreach ($requiredKeys as $key) {
            self::assertArrayHasKey(
                $key,
                $data,
                \sprintf('Expected JSON structure to contain key "%s".', $key),
            );
        }
    }

    /**
     * Skips the test if the DDEV instance is not reachable.
     *
     * This method is idempotent within a process: the first call performs the
     * probe and caches the result; subsequent calls use the cached result.
     */
    protected function skipUnlessInstanceReachable(): void
    {
        if (self::$instanceReachable !== null) {
            if (!self::$instanceReachable) {
                self::markTestSkipped(
                    \sprintf(
                        'DDEV instance not reachable at %s. Start DDEV to run API tests.',
                        self::getBaseUrl(),
                    ),
                );
            }

            return;
        }

        try {
            $probeClient = HttpClient::create([
                'verify_peer' => false,
                'verify_host' => false,
                'timeout' => self::PROBE_TIMEOUT,
            ]);

            $response = $probeClient->request('GET', self::getBaseUrl() . '/api/v1/pages/');
            // Trigger the actual network call — any HTTP response (including 404)
            // means the server is up.
            $response->getStatusCode();
            self::$instanceReachable = true;
        } catch (TransportExceptionInterface $e) {
            self::$instanceReachable = false;
            self::markTestSkipped(
                \sprintf(
                    'DDEV instance not reachable at %s (%s). Start DDEV to run API tests.',
                    self::getBaseUrl(),
                    $e->getMessage(),
                ),
            );
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->skipUnlessInstanceReachable();
    }
}
