# ADR-0003: Normalizer Registry Pattern for Content Elements

## Status

Accepted

## Context

TYPO3 content elements (`tt_content`) come in many types (`CType`): `text`, `textmedia`, `image`, `html`, `list` (plugins), and custom types from third-party extensions.

Each CType requires different field mapping. A `textmedia` element has media references; a `text` element does not. Third-party extensions may add entirely new CTypes.

We need a mechanism that:

1. Maps each CType to a normalizer that knows how to transform it into a DTO
2. Is extensible — third-party extensions must be able to register normalizers for their own CTypes
3. Uses no TypoScript, no magic, no runtime reflection

## Decision

Use a **tagged service registry** pattern:

### Interface

```php
interface ContentElementNormalizerInterface
{
    public function supportsCType(string $cType): bool;
    public function normalize(array $data): ContentElementDto;
}
```

### Registry

```php
final class ContentElementNormalizerRegistry
{
    /** @param iterable<ContentElementNormalizerInterface> $normalizers */
    public function __construct(private readonly iterable $normalizers) {}

    public function normalize(array $data): ContentElementDto
    {
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer->supportsCType($data['CType'] ?? '')) {
                return $normalizer->normalize($data);
            }
        }
        return $this->fallback($data);
    }
}
```

### Registration via DI tags

In `Services.yaml`:

```yaml
_instanceof:
  DMF\ContentApi\Normalizer\ContentElementNormalizerInterface:
    tags: ['content_api.content_element_normalizer']
```

Third-party extensions register their own normalizers the same way:

```yaml
# In EXT:my_extension/Configuration/Services.yaml
Vendor\MyExtension\Normalizer\CarouselNormalizer:
  tags: ['content_api.content_element_normalizer']
```

### Priority

Normalizers can be prioritized via the DI tag `priority` attribute. Higher priority normalizers are checked first. This allows overriding core normalizers.

## Consequences

**Positive:**

- Pure PHP, no TypoScript
- Third parties register normalizers with one line of YAML
- Each normalizer is a small, focused, testable class
- The registry is trivially unit-testable
- CType-to-normalizer mapping is explicit

**Negative:**

- Unsupported CTypes fall through to a generic fallback (which returns minimal data)
- No "automatic" rendering of unknown CTypes like TypoScript provides

**Accepted trade-off:**

Explicit is better than implicit. An unknown CType returning minimal data is preferable to silently broken or inconsistent output. The fallback normalizer logs a notice so developers know to add support.
