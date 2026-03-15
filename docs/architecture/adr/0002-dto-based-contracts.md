# ADR-0002: DTO-Based API Contracts

## Status

Accepted

## Context

API responses can be assembled in several ways:

1. **Raw arrays** — `return json_encode($dataArray)`. Fast to implement, no contract guarantees. A renamed key or missing field breaks consumers silently.
2. **TYPO3 Extbase models** — Serialize domain models directly. Couples the API contract to the database schema. Internal field names leak into the public API.
3. **Typed DTOs** — Dedicated readonly classes that represent exactly what the API consumer receives. Decoupled from internal data structures.

## Decision

All API responses are built from **immutable, readonly DTO classes**.

DTOs are the API contract. They define:

- Which fields exist
- What types they have
- What names the consumer sees

Example:

```php
final readonly class PageDto
{
    public function __construct(
        public int $id,
        public string $slug,
        public string $title,
        public string $description,
        /** @var ContentElementDto[] */
        public array $content,
        public MetaDto $meta,
    ) {}
}
```

Rules:

- DTOs live in `Classes/Dto/`
- DTOs are `final readonly`
- DTOs use constructor promotion
- DTOs have no methods beyond the constructor (no business logic)
- DTOs do not reference TYPO3 classes
- DTOs do not contain nullable fields unless the API contract explicitly allows null
- A DTO field change is a potential API-breaking change and must be treated as such

## Consequences

**Positive:**

- Static analysis tools can verify DTO completeness
- IDE autocompletion works for API response structures
- API contract changes are visible in git diffs
- Normalizer tests can assert exact DTO output
- Frontend SDK generators (OpenAPI) can consume these contracts reliably

**Negative:**

- Every new API field requires a DTO change + normalizer change
- More files than a raw-array approach

**Accepted trade-off:**

The additional files are the point — they make the contract explicit. The normalizer-to-DTO mapping is where we want the complexity to live, because that is where TYPO3 internals are translated to public API shapes.
