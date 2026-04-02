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

| Method | Endpoint | Description | Status |
|--------|----------|-------------|--------|
| GET | `/api/v1/pages/{slug}` | Page with SEO, access info, and all content elements | v0.1.0 |
| GET | `/api/v1/navigation/{key}` | Navigation tree by menu key | planned v0.2.0 |
| GET | `/api/v1/media/{uid}` | Media/file reference details | planned v0.2.0 |

### Example Response

```http
GET /api/v1/pages/home
```

```json
{
    "meta": {
        "apiVersion": "1.0",
        "language": "en",
        "site": "main"
    },
    "page": {
        "id": 1,
        "slug": "/home",
        "title": "Home",
        "navTitle": "Home",
        "doktype": 1,
        "description": "",
        "updatedAt": "2026-03-10T08:30:00+00:00",
        "seo": {
            "title": "Home — My Site",
            "description": "Welcome to our site.",
            "canonicalUrl": "https://example.com/home",
            "robots": "index,follow",
            "ogTitle": null,
            "ogDescription": null
        },
        "access": {
            "feGroup": [],
            "starttime": null,
            "endtime": null
        },
        "content": [
            {
                "id": 10,
                "type": "text",
                "headline": "Welcome",
                "properties": {
                    "bodytext": "<p>Hello world</p>"
                },
                "media": []
            }
        ]
    }
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
            id: (int)$row['uid'],
            type: $this->supportsCType(),
            headline: $row['header'] ?? '',
            properties: [
                'title' => $row['header'],
                'custom_field' => $row['tx_myext_field'],
            ],
            media: [],
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

### Local TYPO3 environment via ddev

Prerequisites: [ddev](https://ddev.readthedocs.io/) installed.

```bash
# Start the environment
ddev start

# Install TYPO3 13.4 with the extension and demo content
ddev install-v13

# Install TYPO3 12.4 with the extension and demo content
ddev install-v12

# Install both versions in one step
ddev install-all
```

After installation the sites are available at:

- TYPO3 13.4: `https://v13.content-api.ddev.site/`
- TYPO3 12.4: `https://v12.content-api.ddev.site/`

Backend credentials: `admin` / `Joh316!!`

The extension source is bind-mounted into the container, so any local file change is immediately active without a rebuild. To flush caches after a PHP change:

```bash
ddev exec -d /var/www/html/v13 vendor/bin/typo3 cache:flush
```

### Composer commands (no ddev required)

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
