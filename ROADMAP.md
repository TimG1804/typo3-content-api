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

> Goal: The page endpoint works against a real TYPO3 installation. Project is committable and taggable.

### Immediately (before first commit)

- [x] Initial git commit — all files into version control
- [x] `CONTRIBUTING.md` — contribution guidelines, coding standards, PR workflow
- [x] `.php-cs-fixer.dist.php` — CS config aligned with TYPO3 coding standards

### Implementation correctness

- [ ] Verify `PageQueryService` against real TYPO3 QueryBuilder API (v12 + v13)
- [ ] Verify `ContentQueryService` against real TYPO3 QueryBuilder API
- [ ] Add missing `test-engineer` agent file (`.claude/agents/test-engineer.md`)

### Content element normalizers

- [ ] `TextmediaNormalizer` (CType `textmedia`)
- [ ] `ImageNormalizer` (CType `image`)

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
- [ ] Language overlay handling (multi-language sites)

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
