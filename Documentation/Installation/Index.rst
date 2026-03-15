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

Local Development Environment
==============================

A `ddev <https://ddev.readthedocs.io/>`_ configuration is included to run both supported TYPO3 versions locally. PHP 8.2, Apache, and MariaDB are preconfigured.

Start the environment:

..  code-block:: bash

    ddev start

Install a TYPO3 instance with the extension and the Introduction Package as demo content:

..  code-block:: bash

    ddev install-v13    # TYPO3 13.4 LTS
    ddev install-v12    # TYPO3 12.4 LTS
    ddev install-all    # both versions

After installation:

-  TYPO3 13.4: ``https://v13.content-api.ddev.site/``
-  TYPO3 12.4: ``https://v12.content-api.ddev.site/``
-  Backend credentials: ``admin`` / ``Joh316!!``

The extension source is bind-mounted at ``/var/www/content_api`` inside the container. Local file changes are reflected immediately. Flush caches after PHP changes:

..  code-block:: bash

    ddev exec -d /var/www/html/v13 vendor/bin/typo3 cache:flush
