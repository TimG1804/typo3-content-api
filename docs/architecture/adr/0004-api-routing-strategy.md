# ADR-0004: PSR-15 Middleware for API Routing

## Status

Accepted

## Context

TYPO3 offers several mechanisms to handle custom routes:

1. **Extbase plugin routing** — ties the API to the Extbase request lifecycle, TypoScript plugin configuration, and a specific page. Adds unnecessary overhead and coupling.
2. **Route enhancers** — designed for decorating existing page URLs, not for standalone API endpoints.
3. **Custom `ext_localconf.php` route registration** — possible but not standardized across TYPO3 versions.
4. **PSR-15 middleware** — registered via `Configuration/RequestMiddlewares.php`. Intercepts the request early, before TYPO3 page rendering. Full control over request/response.

## Decision

API routes are handled by a **PSR-15 middleware** registered in `Configuration/RequestMiddlewares.php`.

The middleware:

- Is positioned **after** `typo3/cms-frontend/site` (so `SiteInterface` and language context are available on the request)
- Is positioned **before** `typo3/cms-frontend/page-resolver` (so TYPO3 never attempts page rendering for API routes)
- Matches requests to `/api/v1/*` paths
- Dispatches to the appropriate controller
- Returns a `JsonResponse` directly

```php
// Configuration/RequestMiddlewares.php
return [
    'frontend' => [
        'dmf/content-api/routing' => [
            'target' => \DMF\ContentApi\Middleware\ApiRoutingMiddleware::class,
            'before' => ['typo3/cms-frontend/page-resolver'],
            'after' => ['typo3/cms-frontend/site'],
        ],
    ],
];
```

### Route resolution

Routes are resolved by a simple internal router within the middleware. No Extbase, no TypoScript, no page tree dependency.

Initial routes:

```
GET /api/v1/pages/{slug}       → PageController
GET /api/v1/navigation/{key}   → NavigationController
GET /api/v1/media/{id}         → MediaController
```

## Consequences

**Positive:**

- API requests never enter the TYPO3 page rendering pipeline
- Full control over request handling, error responses, content negotiation
- No dependency on Extbase, TypoScript, or specific page records
- Site and language context are available (resolved by upstream TYPO3 middleware)
- Clean separation: TYPO3 pages = content data, not routing targets

**Negative:**

- No automatic integration with TYPO3 backend modules for route inspection
- Route definitions live in PHP, not in site configuration

**Accepted trade-off:**

The API layer is deliberately independent of TYPO3's page-based routing. PHP route definitions are explicit, testable, and version-controlled. Backend module integration for route inspection can be added later if needed.
