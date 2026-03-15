<?php

declare(strict_types=1);

namespace DMF\ContentApi\Routing;

use DMF\ContentApi\Controller\PageController;

/**
 * Registers all core API routes.
 *
 * Third-party extensions can add routes by creating their own
 * RouteRegistrar-style class and calling $router->addRoute() in Services.yaml
 * or via a PSR-14 event (to be added).
 */
final class RouteRegistrar
{
    public function __construct(
        private readonly Router $router,
    ) {
        $this->registerCoreRoutes();
    }

    private function registerCoreRoutes(): void
    {
        $this->router->addRoute('GET', '/pages/{slug}', PageController::class, 'show');
    }
}
