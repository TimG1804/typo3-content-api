..  include:: /Includes.rst.txt

============
Introduction
============

What does it do?
================

The TYPO3 Content API extension provides a true API-first headless layer for
TYPO3. Unlike traditional approaches that render TypoScript page objects as
JSON, this extension:

-  Queries content directly from the TYPO3 database
-  Normalizes records through explicit PHP normalizer classes
-  Serializes results into immutable DTO-based responses
-  Serves JSON via PSR-15 middleware, completely outside the TypoScript
   rendering pipeline

The extension is designed for modern frontend consumers such as Next.js,
Nuxt, React Native, and other API-driven applications.

Key principles
==============

-  **TYPO3 as content repository** -- not as a JSON renderer
-  **Stable API contracts** -- DTO-based, versioned responses
-  **Extensibility** -- register custom normalizers, query services, and
   endpoints via standard TYPO3 dependency injection
-  **No TypoScript** -- explicit PHP architecture throughout
