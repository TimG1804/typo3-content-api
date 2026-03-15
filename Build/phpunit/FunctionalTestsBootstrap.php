<?php

declare(strict_types=1);

require __DIR__ . '/../../.Build/vendor/autoload.php';

// Define ORIGINAL_ROOT so typo3/testing-framework can create the test instance path.
// This constant must be set before any FunctionalTestCase::setUp() runs.
(new \TYPO3\TestingFramework\Core\Testbase())->defineOriginalRootPath();
