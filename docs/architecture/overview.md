# Architecture Overview

## Purpose

`3m5/typo3-content-api` (EXT:content_api) is a TYPO3 extension that provides a REST API for content delivery. TYPO3 acts as the content repository; the extension provides the API layer.

## Namespace

```
DMF\ContentApi\
```

## Data Flow

Every API request follows this path:

```
HTTP Request
  → PSR-15 Middleware (ApiRoutingMiddleware)
    → Controller
      → Query Service (reads from TYPO3)
        → Normalizer (transforms TYPO3 data → DTO)
          → Serializer (DTO → JSON)
            → HTTP Response
```

No TypoScript rendering is involved at any stage.

## Layer Responsibilities

### Middleware (`Middleware/`)

Intercepts requests matching `/api/v1/*`. Resolves the route, dispatches to the correct controller. Handles content negotiation and CORS.

Position in TYPO3 middleware stack: after `typo3/cms-frontend/site` (so site and language context are available), before `typo3/cms-frontend/page-resolver` (so TYPO3 page rendering is never triggered for API routes).

### Controllers (`Controller/`)

Thin request handlers. Parse request parameters, call query services, return serialized responses. Controllers do not contain business logic or TYPO3 API calls.

### Query Services (`Query/`)

Read data from TYPO3 using `QueryBuilder`, `PageRepository`, `SiteFinder`, or FAL APIs. Return raw TYPO3 data arrays. Each query service has an interface for substitution/extension.

Query services are the **only layer** that interacts with TYPO3 core APIs directly.

### Normalizers (`Normalizer/`)

Transform TYPO3 data arrays into DTOs. This is where TYPO3 field names are mapped to public API field names.

Content element normalizers implement `ContentElementNormalizerInterface` and are registered via DI tags in the `ContentElementNormalizerRegistry`.

### DTOs (`Dto/`)

Immutable, readonly PHP classes. They define the **public API contract**. A DTO change = a potential breaking change.

DTOs are not TYPO3 models. They represent what the API consumer sees.

### Serializer (`Serializer/`)

Converts DTOs to JSON using the Symfony Serializer component.

### Events (`Event/`)

PSR-14 events dispatched at key points in the pipeline. Third-party extensions can listen to modify behavior (e.g., enrich DTOs, modify queries, add cache tags).

### Cache (`Cache/`)

TYPO3 Cache Framework integration. Caches serialized responses. Provides cache tag management for fine-grained invalidation.

### OpenAPI (`OpenApi/`)

Providers that contribute endpoint definitions to the generated OpenAPI specification. Each module can register its own `OpenApiProviderInterface`.

## Dependency Rules

```
Controller → Query Service (interface)
Controller → Normalizer (interface)
Controller → Serializer (interface)

Query Service → TYPO3 Core APIs
Normalizer → DTOs
Serializer → DTOs

Nothing → Controller (controllers are entry points only)
DTOs → nothing (leaf nodes, no dependencies)
```

## Extension Points for Third Parties

1. **Custom content element normalizer** — implement `ContentElementNormalizerInterface`, tag with `content_api.content_element_normalizer`
2. **Custom query service** — implement the relevant `*QueryServiceInterface`, override in `Services.yaml`
3. **Custom endpoint** — register additional PSR-15 middleware or extend the router
4. **Custom DTO** — extend or wrap existing DTOs via events
5. **Custom OpenAPI provider** — implement `OpenApiProviderInterface`
6. **Event listeners** — listen to PSR-14 events for pre/post processing

## API Versioning

API version is encoded in the URL path: `/api/v1/...`

A new major version (v2) is introduced only when DTO structures change in backwards-incompatible ways. Minor additions (new fields) are non-breaking within a version.

## TYPO3 Compatibility

- TYPO3 v12.4 LTS
- TYPO3 v13.4 LTS
- PHP 8.2+

Where v12 and v13 APIs differ, compatibility is handled in the query service layer only. All other layers are TYPO3-version-agnostic.
