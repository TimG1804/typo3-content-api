..  include:: /Includes.rst.txt

=============
Configuration
=============

The extension requires no TypoScript configuration. All API routes are
registered automatically via PSR-15 middleware.

The API is available at ``/api/v1/`` relative to your TYPO3 site base URL.

Site configuration
==================

Ensure your TYPO3 site configuration has a valid ``base`` setting. The API
middleware uses the site configuration to resolve language context.
