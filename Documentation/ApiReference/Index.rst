..  include:: /Includes.rst.txt

=============
API Reference
=============

Base URL
========

All endpoints are served under ``/api/v1/`` relative to your TYPO3 site base
URL.

Endpoints
=========

GET /api/v1/pages/{slug}
------------------------

Returns the page record and all associated content elements for the given
page slug.

**Parameters:**

-  ``slug`` (string, required) -- the page slug without leading slash

**Response:** ``200 OK``

..  code-block:: json

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

**Error responses:**

-  ``404 Not Found`` -- page with given slug does not exist

GET /api/v1/navigation/{key}
----------------------------

Returns the navigation tree for the given menu key.

GET /api/v1/media/{uid}
-----------------------

Returns file reference details for the given uid.
