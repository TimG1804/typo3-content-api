..  include:: /Includes.rst.txt

============
Installation
============

Requirements
============

-  PHP ^8.2
-  TYPO3 ^12.4 or ^13.4

Installation via Composer
=========================

..  code-block:: bash

    composer require 3m5/typo3-content-api

Then activate the extension:

..  code-block:: bash

    vendor/bin/typo3 extension:activate content_api

Flush all caches after activation:

..  code-block:: bash

    vendor/bin/typo3 cache:flush
