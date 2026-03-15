# Contributing to typo3-content-api

Thank you for your interest in contributing. This project is an engineering-focused open-source TYPO3 extension that provides a true API-first headless layer. Contributions that improve correctness, architecture, test coverage, documentation, and extensibility are welcome.

This document is the single source of truth for getting your contribution from idea to merge.

---

## Table of contents

- [What contributions are welcome](#what-contributions-are-welcome)
- [Prerequisites](#prerequisites)
- [Setup](#setup)
- [Coding standards](#coding-standards)
- [Architecture boundaries](#architecture-boundaries)
- [Branching model](#branching-model)
- [Commit messages](#commit-messages)
- [Pull request process](#pull-request-process)
- [Tests](#tests)
- [Adding content element support](#adding-content-element-support)
- [Reporting bugs and requesting features](#reporting-bugs-and-requesting-features)
- [Code of conduct](#code-of-conduct)

---

## What contributions are welcome

- Bug fixes with a regression test
- New content element normalizers (see [Adding content element support](#adding-content-element-support))
- New API endpoints that follow the existing layered architecture
- Improvements to existing query services, normalizers, DTOs, or serializers
- Test coverage improvements
- Documentation improvements
- CI/tooling improvements

Please open an issue before starting work on a new feature or a breaking change so the approach can be discussed before implementation.

---

## Prerequisites

- PHP 8.2 or higher
- Composer 2.x
- Git
- [ddev](https://ddev.readthedocs.io/) (recommended for local TYPO3 testing)

---

## Setup

```bash
git clone https://github.com/3m5/typo3-content-api.git
cd typo3-content-api
composer install
```

The vendor directory is placed under `.Build/vendor/` by design. All Composer scripts use that path.

**Local TYPO3 environment (recommended for integration testing):**

A ddev configuration is included. Start it and install one or both TYPO3 versions:

```bash
ddev start
ddev install-v13    # TYPO3 13.4 at https://v13.content-api.ddev.site/
ddev install-v12    # TYPO3 12.4 at https://v12.content-api.ddev.site/
```

The extension source is bind-mounted inside the container, so local changes are active immediately. Backend credentials: `admin` / `Joh316!!`

**Run the unit test suite:**

```bash
composer test -- --testsuite Unit
```

**Run the functional test suite** (requires SQLite):

```bash
composer test -- --testsuite Functional
```

**Run all tests:**

```bash
composer test
```

**Check coding standards** (dry run, shows diff):

```bash
composer cs-check
```

**Fix coding standards automatically:**

```bash
composer cs-fix
```

CI runs both the CS check and the full test matrix (PHP 8.2–8.4 x TYPO3 12.4/13.4) on every push and pull request. A PR must pass CI before it can be merged.

---

## Coding standards

- **Strict types**: every PHP file must start with `declare(strict_types=1);`
- **PHP 8.2+ features**: use constructor property promotion, enums, intersection types, and readonly properties where appropriate
- **PSR-12** style enforced via `php-cs-fixer` (see `.php-cs-fixer.dist.php`)
- **Small, focused classes**: one responsibility per class
- **Constructor injection**: use TYPO3 dependency injection exclusively; no `GeneralUtility::makeInstance()` for own classes
- **Immutable DTOs**: DTOs must not have setters; pass all values through the constructor
- **Interfaces for all extension points**: every injectable service that third parties may replace must have an interface
- **Descriptive names**: class names, method names, and variable names must be self-explanatory; avoid abbreviations
- **No `@suppress` annotations** to silence type errors; fix the actual type issue

---

## Architecture boundaries

The extension is structured as a strict layered pipeline:

```
Request
  -> ApiRoutingMiddleware (PSR-15)
     -> Controller
        -> QueryService
           -> Normalizer
              -> DTO
                 -> JsonSerializer
                    -> JSON Response
```

**Rules that apply to all contributions:**

- **No TypoScript in the data path.** Page and content rendering must not depend on a TypoScript setup. PHP services own the rendering logic.
- **No raw TYPO3 database field names in public API contracts.** DTOs use API-friendly property names. The normalizer is the translation boundary between TYPO3 internals and the public contract.
- **Controllers are thin.** A controller resolves the request parameters, delegates to a query service, and hands the result to the serializer. Business logic belongs in query services and normalizers.
- **DTOs are immutable value objects.** They represent the public API contract. Changes to DTOs are potentially breaking changes and require a changelog entry.
- **Query services implement an interface.** `PageQueryServiceInterface`, `ContentQueryServiceInterface`, etc. are the contracts; the implementations may change.
- **Normalizers are registered, not hardcoded.** Content element normalizers must implement `ContentElementNormalizerInterface` and be registered via the DI tag (see [Adding content element support](#adding-content-element-support)).
- **New extension points must ship with an interface.** If you add a new injectable collaborator, define the interface first.

Breaking any of these boundaries without prior discussion in an issue will result in the PR being closed.

---

## Branching model

| Branch pattern | Purpose |
|---|---|
| `main` | Stable, always releasable |
| `feature/short-description` | New features |
| `fix/short-description` | Bug fixes |
| `docs/short-description` | Documentation-only changes |
| `chore/short-description` | Tooling, CI, dependency bumps |

Branch from `main`. Submit your pull request back to `main`.

Do not commit directly to `main`.

---

## Commit messages

Use [Conventional Commits](https://www.conventionalcommits.org/) format:

```
<type>: <short imperative summary>
```

Types:

| Type | When to use |
|---|---|
| `feat` | A new feature or endpoint |
| `fix` | A bug fix |
| `docs` | Documentation only |
| `refactor` | Code restructuring without behavior change |
| `test` | Adding or fixing tests |
| `chore` | Tooling, CI, dependency updates, release prep |

**Examples:**

```
feat: add TextmediaNormalizer for CType textmedia
fix: resolve null pointer in PageNormalizer when no content exists
test: add unit tests for NavigationNormalizer
docs: document extension point for custom normalizers
chore: update php-cs-fixer to 3.x
```

Keep the summary line under 72 characters. If more context is needed, add a blank line followed by a body paragraph.

---

## Pull request process

1. **Open an issue first** for non-trivial changes or any change that affects public contracts, architecture, or extension points.
2. **Create a feature or fix branch** from `main`.
3. **Write tests** for the changed behavior. PRs without tests for new behavior will not be merged.
4. **Update `CHANGELOG.md`** under the `[Unreleased]` section using the [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) format.
5. **Run CI locally** before pushing: `composer cs-check && composer test`
6. **Keep commits atomic.** Each commit should represent one logical change and pass CI on its own.
7. **No breaking changes without prior issue discussion.** A breaking change is anything that changes a public DTO shape, removes or renames a public interface, or alters the API response structure.
8. **Fill in the PR description.** Explain what the change does, why it is needed, and how it was tested.

PRs that fail CI, lack tests, or do not follow these conventions will be asked to revise before review.

---

## Tests

The test suite is split into two suites:

| Suite | Location | What it tests |
|---|---|---|
| Unit | `Tests/Unit/` | Normalizers, DTOs, serializer, router — isolated, no TYPO3 bootstrap |
| Functional | `Tests/Functional/` | Endpoints, query services, full TYPO3 context via testing framework |

**Expectations for contributions:**

- New normalizers: unit test covering supported CType, property mapping, and edge cases (empty fields, null values)
- New DTOs: unit test asserting constructor and property access
- New endpoints: functional test covering happy path, 404, and error response shape
- Bug fixes: regression test that fails before the fix and passes after

Use the TYPO3 Testing Framework fixtures and database setup helpers for functional tests. The CI matrix runs functional tests with `pdo_sqlite`.

---

## Adding content element support

To add support for a new TYPO3 CType, implement the `ContentElementNormalizerInterface`:

```php
namespace YourVendor\YourExtension\Normalizer\ContentElement;

use DMF\ContentApi\Normalizer\ContentElementNormalizerInterface;
use DMF\ContentApi\Dto\ContentElementDto;

final class MyCustomNormalizer implements ContentElementNormalizerInterface
{
    public function supports(string $cType): bool
    {
        return $cType === 'my_ctype';
    }

    public function normalize(array $record): ContentElementDto
    {
        return new ContentElementDto(
            id: (int)$record['uid'],
            type: $record['CType'],
            content: [
                // map fields here — use API-friendly names, not raw DB column names
            ],
        );
    }
}
```

Register it in your extension's `Configuration/Services.yaml`:

```yaml
YourVendor\YourExtension\Normalizer\ContentElement\MyCustomNormalizer:
  tags:
    - name: content_api.content_element_normalizer
```

The `ContentElementNormalizerRegistry` picks up all tagged services automatically via DI.

If you are contributing a normalizer for a **TYPO3 core CType** (e.g., `textmedia`, `image`, `bullets`), add it directly to `Classes/Normalizer/ContentElement/` in this repository and include unit tests.

---

## Reporting bugs and requesting features

Use [GitHub Issues](https://github.com/3m5/typo3-content-api/issues).

**For bugs, include:**

- TYPO3 version
- PHP version
- Extension version or commit hash
- Minimal reproduction steps
- Actual behavior vs. expected behavior
- Relevant log output or stack trace

**For feature requests, include:**

- The problem you are trying to solve
- Why existing extension points are not sufficient
- A rough description of the proposed API or interface

Security vulnerabilities should not be reported as public issues. Contact the maintainers directly via the repository's security advisory feature on GitHub.

---

## Code of conduct

A formal Code of Conduct document will be added in a future release. Until then: be professional, constructive, and respectful. Contributions are reviewed on technical merit. Personal attacks, harassment, or discriminatory language will not be tolerated and will result in permanent exclusion from the project.
