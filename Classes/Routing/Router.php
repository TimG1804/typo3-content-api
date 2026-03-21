<?php

declare(strict_types=1);

namespace DMF\ContentApi\Routing;

final class Router
{
    private const API_PREFIX = '/api/v1';

    /** @var RouteDefinition[] */
    private array $routes = [];

    public function addRoute(string $method, string $pattern, string $controllerClass, string $action): void
    {
        $this->routes[] = new RouteDefinition(
            method: strtoupper($method),
            pattern: self::API_PREFIX . $pattern,
            controllerClass: $controllerClass,
            action: $action,
        );
    }

    /**
     * @return array{route: RouteDefinition, params: array<string, string>}|null
     */
    public function match(string $method, string $path): ?array
    {
        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            if ($route->method !== $method) {
                continue;
            }

            $params = $this->matchPattern($route->pattern, $path);
            if ($params !== null) {
                return ['route' => $route, 'params' => $params];
            }
        }

        return null;
    }

    public function isApiPath(string $path): bool
    {
        return str_starts_with($path, self::API_PREFIX . '/');
    }

    /**
     * @return array<string, string>|null
     */
    private function matchPattern(string $pattern, string $path): ?array
    {
        $regex = preg_replace('/\{([a-zA-Z_]+)}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $path, $matches)) {
            return array_filter($matches, fn($key) => \is_string($key), ARRAY_FILTER_USE_KEY);
        }

        return null;
    }
}
