# Roadmap

## Status legend

- [ ] Open
- [x] Done
- [~] In progress

---

## Phase 0 — Project Bootstrap

> Goal: Clean, publishable project skeleton with all OSS essentials.

- [x] Git repository initialized
- [x] `composer.json` with TYPO3 v12/v13 and PHP 8.2+ constraints
- [x] `ext_emconf.php` (state: experimental)
- [x] `Configuration/Services.yaml` with DI baseline
- [x] Architecture overview + ADR-0001 to ADR-0004
- [x] DTO layer (PageDto, ContentElementDto, MediaDto, NavigationItemDto, ErrorDto, …)
- [x] Normalizer interfaces + ContentElementNormalizerRegistry
- [x] Query service interfaces (Page, Content, Navigation, Media)
- [x] Vertical slice: page endpoint (PageController + PageQueryService + PageNormalizer + Router + JsonSerializer + Middleware)
- [x] PHPUnit setup + first unit tests
- [x] Full GPL-3.0 LICENSE
- [x] README.md
- [x] CHANGELOG.md
- [x] .gitattributes
- [x] Documentation/ for TER (RST)
- [x] .github/workflows/ci.yml (PHP 8.2–8.4 × TYPO3 12/13 matrix)

---

## Phase 1 — First Working Release (0.1.0) experimental

> Goal: The page endpoint works correctly against a real TYPO3 installation including multi-language sites. Project is committable and taggable.

### Project setup

- [x] Initial git commit — all files into version control
- [x] `CONTRIBUTING.md` — contribution guidelines, coding standards, PR workflow
- [x] `.php-cs-fixer.dist.php` — CS config aligned with TYPO3 coding standards
- [x] `ddev` setup for local multi-version TYPO3 development (v12 + v13)
- [x] API/E2E contract tests (35+ cases via `Tests/Api/`)

### Implementation correctness

- [x] Verify `PageQueryService` against real TYPO3 QueryBuilder API (v12 + v13)
- [x] Verify `ContentQueryService` against real TYPO3 QueryBuilder API
- [x] Add missing `test-engineer` agent file (`.claude/agents/test-engineer.md`)

### Page DTO completeness

- [x] Extend `PageDto` with: `navTitle`, `doktype`, `updatedAt`
- [x] Add `SeoDto` nested object: `title` (seo_title fallback title), `canonicalUrl`, `robots` (derived from no_index + no_follow), `ogTitle`, `ogDescription` — nullable if EXT:seo not installed
- [x] Add `AccessDto` nested object: `feGroups` (array of GIDs), `starttime` (nullable), `endtime` (nullable), `extendToSubpages`

### Multi-language support

- [ ] Derive active language from TYPO3 `SiteInterface` + request URL (Site Handling API) — no magic query parameters
- [ ] Apply language overlays in `PageQueryService`
- [ ] Apply language overlays in `ContentQueryService`
- [ ] `MetaDto` returns resolved language identifier

### Content element normalizers

- [x] `TextmediaNormalizer` (CType `textmedia`)
- [ ] `ImageNormalizer` (CType `image`)
- [ ] `HtmlNormalizer` (CType `html`)
- [ ] Map `imageorient` integer to semantic string in `mediaPosition` (e.g. `above-center`, `intext-right`) to avoid leaking TYPO3 internal encoding into the API contract

### Backend UX

- [ ] Backend info panel — readonly TCA field on the `pages` table showing the API URL (`GET /api/v1/pages/{slug}`), populated via a TCA `DataProvider`. Design must allow future endpoints (navigation, media) to register their own info entries.

### Release readiness

- [ ] Extension icon — add `Resources/Public/Icons/Extension.svg` (required for TER upload)
- [ ] CI matrix — add `php: 8.4` × `typo3: ^12.4` combination (TYPO3 12 supports PHP 8.4 officially)

### Git

- [ ] Tag `0.1.0` after first clean CI run

---

## Phase 2 — Second Vertical Slice (0.2.0)

> Goal: Navigation endpoint works. PSR-14 events are in place for extensibility.

- [ ] `NavigationController` + `NavigationQueryService` implementation
- [ ] `MediaController` + `MediaQueryService` implementation
- [ ] PSR-14 events: `BeforeContentNormalizationEvent`, `AfterPageResponseEvent`
- [ ] Functional tests for page and navigation endpoints

---

## Phase 3 — Stability & Performance (0.3.0)

> Goal: Production-ready caching and workspace awareness.

- [ ] Cache layer (`CacheServiceInterface` + TYPO3 Cache Framework integration)
- [ ] Cache tags per page and content element
- [ ] TYPO3 workspace preview support

---

## Phase 4 — Developer Experience (0.4.0)

> Goal: OpenAPI spec and contributor tooling.

- [ ] `OpenApiProviderInterface` + `CoreOpenApiProvider`
- [ ] OpenAPI 3.1 spec auto-generation endpoint (`GET /api/v1/openapi.json`)
- [ ] GitHub issue + PR templates
- [ ] `SECURITY.md`
- [ ] `CODE_OF_CONDUCT.md`

---

## Backlog (unscheduled)

- [ ] Authentication / authorization extension point
- [ ] Rate limiting extension point
- [ ] Form handling (EXT:form integration)
- [ ] News integration example (third-party normalizer reference implementation)
- [ ] Packagist submission
- [ ] TER submission
- [ ] TYPO3 v14 compatibility check (when released)
