# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.0] - 2026-03-15

### Added

- PSR-15 middleware-based API routing (`/api/v1/*`)
- Page endpoint (`GET /api/v1/pages/{slug}`) with content elements
- DTO-based API contracts: `PageDto`, `ContentElementDto`, `MediaDto`, `NavigationItemDto`, `ErrorDto`
- Normalizer registry pattern with DI tag-based registration
- `ContentElementNormalizerInterface` for third-party CType support
- Built-in `TextNormalizer` for CType `text`
- Query service layer with interfaces for page, content, navigation, and media queries
- Symfony Serializer integration for JSON serialization
- Interface-based DI bindings for all extension points
- TYPO3 v12 and v13 support
- PHP 8.2+ support
- Unit tests for DTOs, normalizers, serializer, and router

[Unreleased]: https://github.com/3m5/typo3-content-api/compare/0.1.0...HEAD
[0.1.0]: https://github.com/3m5/typo3-content-api/releases/tag/0.1.0
