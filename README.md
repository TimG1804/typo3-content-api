# TYPO3 Content API

[![CI](https://github.com/3m5/typo3-content-api/actions/workflows/ci.yml/badge.svg)](https://github.com/3m5/typo3-content-api/actions/workflows/ci.yml)
[![Latest Version](https://img.shields.io/packagist/v/3m5/typo3-content-api)](https://packagist.org/packages/3m5/typo3-content-api)
[![TYPO3](https://img.shields.io/badge/TYPO3-12%20%7C%2013-orange)](https://typo3.org/)
[![PHP](https://img.shields.io/badge/PHP-%5E8.2-blue)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL--3.0--or--later-green)](LICENSE)

A true **API-first headless extension** for TYPO3. Treats TYPO3 as a content repository and exposes structured JSON endpoints with stable, versioned contracts.

**This is not a TypoScript JSON renderer.** Content is queried directly from the database, normalized through explicit PHP classes, and serialized into DTO-based responses.

## Key Features

- **No TypoScript rendering pipeline** -- pure PHP request handling via PSR-15 middleware
- **DTO-based API contracts** -- immutable, typed response objects
- **Normalizer registry** -- per-CType normalizers, extensible by third-party extensions
- **PSR-14 events** -- hook into normalization and response lifecycle
- **Symfony Serializer** -- battle-tested serialization layer
- **TYPO3 v12 + v13** -- supports both LTS and current

## Requirements

- PHP ^8.2
- TYPO3 ^12.4 or ^13.4

## Installation

```bash
composer require 3m5/typo3-content-api
```

Then activate the extension in TYPO3:

```bash
vendor/bin/typo3 extension:activate content_api
```

## API Endpoints

All endpoints are served under `/api/v1/` relative to your TYPO3 site base URL.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/pages/{slug}` | Page content with all content elements |
| GET | `/api/v1/navigation/{key}` | Navigation tree by menu key |
| GET | `/api/v1/media/{uid}` | Media/file reference details |

### Example Response

```http
GET /api/v1/pages/home
```

```json
{
    "meta": {
        "apiVersion": "1.0",
        "language": "en",
        "generatedAt": "2026-03-15T12:00:00+00:00"
    },
    "page": {
        "uid": 1,
        "title": "Home",
        "slug": "/home",
        "doktype": 1,
        "lastModified": "2026-03-10T08:30:00+00:00"
    },
    "content": [
        {
            "uid": 10,
            "type": "text",
            "colPos": 0,
            "data": {
                "header": "Welcome",
                "bodytext": "<p>Hello world</p>"
            }
        }
    ]
}
```

## Extending the API

### Custom Content Element Normalizer

Register a normalizer for your custom CType:

```php
// Classes/Normalizer/ContentElement/MyCustomNormalizer.php
namespace Vendor\MyExt\Normalizer\ContentElement;

use DMF\ContentApi\Dto\ContentElementDto;
use DMF\ContentApi\Normalizer\ContentElementNormalizerInterface;

final class MyCustomNormalizer implements ContentElementNormalizerInterface
{
    public function supportsCType(): string
    {
        return 'my_custom_ctype';
    }

    public function normalize(array $row): ContentElementDto
    {
        return new ContentElementDto(
            uid: $row['uid'],
            type: $this->supportsCType(),
            colPos: $row['colPos'],
            data: [
                'title' => $row['header'],
                'custom_field' => $row['tx_myext_field'],
            ],
        );
    }
}
```

Register it in your extension's `Configuration/Services.yaml`:

```yaml
services:
  Vendor\MyExt\Normalizer\ContentElement\MyCustomNormalizer:
    tags: ['content_api.content_element_normalizer']
```

### Custom Query Service

Override an existing query service by aliasing the interface:

```yaml
services:
  DMF\ContentApi\Query\PageQueryServiceInterface:
    alias: Vendor\MyExt\Query\CustomPageQueryService
```

## Development

```bash
# Install dependencies
composer install

# Run tests
composer test

# Check coding standards
composer cs-check

# Fix coding standards
composer cs-fix
```

## Architecture

See [docs/architecture/overview.md](docs/architecture/overview.md) for the full architecture overview and [docs/architecture/adr/](docs/architecture/adr/) for Architecture Decision Records.

## License

This project is licensed under the [GPL-3.0-or-later](LICENSE).

## Credits

Developed by [3m5. GmbH](https://www.3m5.de).
