<?php

declare(strict_types=1);

namespace DMF\ContentApi\Routing;

final readonly class RouteDefinition
{
    public function __construct(
        public string $method,
        public string $pattern,
        public string $controllerClass,
        public string $action,
    ) {}
}
