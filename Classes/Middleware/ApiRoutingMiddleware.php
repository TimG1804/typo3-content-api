<?php

declare(strict_types=1);

namespace DMF\ContentApi\Middleware;

use DMF\ContentApi\Dto\ErrorDto;
use DMF\ContentApi\Routing\Router;
use DMF\ContentApi\Routing\RouteRegistrar;
use DMF\ContentApi\Serializer\SerializerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\JsonResponse;

final class ApiRoutingMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly Router $router,
        private readonly SerializerInterface $serializer,
        private readonly ContainerInterface $container,
        RouteRegistrar $routeRegistrar,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        if (!$this->router->isApiPath($path)) {
            return $handler->handle($request);
        }

        $method = $request->getMethod();
        $match = $this->router->match($method, $path);

        if ($match === null) {
            return $this->errorResponse(404, 'Not Found', 'No API route matches "' . $method . ' ' . $path . '".');
        }

        $route = $match['route'];
        $params = $match['params'];

        $controller = $this->container->get($route->controllerClass);

        return $controller->{$route->action}($request, ...array_values($params));
    }

    private function errorResponse(int $status, string $error, string $message): ResponseInterface
    {
        $errorDto = new ErrorDto(status: $status, error: $error, message: $message);

        return new JsonResponse(
            json_decode($this->serializer->serialize($errorDto), true),
            $status,
            ['Content-Type' => 'application/json'],
        );
    }
}
