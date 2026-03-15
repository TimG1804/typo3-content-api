# ADR-0001: No TypoScript Rendering Pipeline

## Status

Accepted

## Context

TYPO3's standard content delivery works through the TypoScript PAGE rendering pipeline: a request hits the frontend, TYPO3 resolves a page, renders content elements via TypoScript, and returns HTML (or JSON, if configured).

EXT:headless follows this pattern — it replaces the HTML output with JSON but still uses TypoScript for content element rendering, data mapping, and response assembly.

This approach has drawbacks for API consumers:

- API response structure is defined in TypoScript, not in typed PHP code
- Responses are hard to test in isolation
- Contracts are implicit (whatever TypoScript produces) rather than explicit (typed DTOs)
- Changing a TypoScript configuration can silently break frontend consumers
- TypoScript is unfamiliar to frontend developers consuming the API
- No static analysis or IDE support for TypoScript-based contracts

## Decision

EXT:content_api does **not** use the TYPO3 TypoScript rendering pipeline for API responses.

Instead:

- API routes are handled by a PSR-15 middleware that intercepts requests before the TYPO3 page resolver
- Controllers dispatch to query services that read data via TYPO3 PHP APIs (QueryBuilder, PageRepository, FAL)
- Normalizers transform raw TYPO3 data into typed DTOs
- The Symfony Serializer converts DTOs to JSON

TypoScript is not used for:

- Content element rendering
- Field mapping
- Response assembly
- Data transformation

## Consequences

**Positive:**

- API contracts are defined in PHP (DTOs) — fully typed, testable, IDE-supported
- Breaking changes are detectable at compile time
- Each layer is independently testable
- Frontend developers see a stable, documented API — not a TypoScript-derived output
- Extension developers use PHP interfaces, not TypoScript overrides

**Negative:**

- Every content element needs a PHP normalizer (no "free" rendering via TypoScript)
- Existing TypoScript-based content rendering cannot be reused
- Initial development effort is higher than wrapping TypoScript output

**Accepted trade-off:**

The higher initial effort is justified by the stability, testability, and developer experience gains. The normalizer registry pattern keeps the per-CType effort manageable.
