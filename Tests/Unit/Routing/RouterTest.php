<?php

declare(strict_types=1);

namespace DMF\ContentApi\Tests\Unit\Routing;

use DMF\ContentApi\Routing\Router;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    #[Test]
    public function matchesRouteWithParameter(): void
    {
        $router = new Router();
        $router->addRoute('GET', '/pages/{slug}', 'SomeController', 'show');

        $result = $router->match('GET', '/api/v1/pages/home');

        self::assertNotNull($result);
        self::assertSame('SomeController', $result['route']->controllerClass);
        self::assertSame('show', $result['route']->action);
        self::assertSame(['slug' => 'home'], $result['params']);
    }

    #[Test]
    public function returnsNullForUnknownRoute(): void
    {
        $router = new Router();
        $router->addRoute('GET', '/pages/{slug}', 'SomeController', 'show');

        $result = $router->match('GET', '/api/v1/unknown/path');

        self::assertNull($result);
    }

    #[Test]
    public function doesNotMatchWrongMethod(): void
    {
        $router = new Router();
        $router->addRoute('GET', '/pages/{slug}', 'SomeController', 'show');

        $result = $router->match('POST', '/api/v1/pages/home');

        self::assertNull($result);
    }

    #[Test]
    public function isApiPathDetectsCorrectly(): void
    {
        $router = new Router();

        self::assertTrue($router->isApiPath('/api/v1/pages/home'));
        self::assertTrue($router->isApiPath('/api/v1/navigation/main'));
        self::assertFalse($router->isApiPath('/some/other/path'));
        self::assertFalse($router->isApiPath('/api/v1'));
    }
}
