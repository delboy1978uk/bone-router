<?php

namespace Bone\Test\Router;

use Barnacle\Container;
use Barnacle\ExceptionotFoundException;
use Exception;
use Bone\Firewall\FirewallPackage;
use Bone\Firewall\RouteFirewall;
use Bone\Http\Middleware\HalCollection;
use Bone\Http\Middleware\HalEntity;
use Bone\Http\Middleware\Stack;
use Bone\I18n\Form;
use Bone\I18n\Http\Middleware\I18nMiddleware;
use Bone\I18n\I18nPackage;
use Bone\I18n\Service\TranslatorFactory;
use Bone\I18n\View\Extension\LocaleLink;
use Bone\I18n\View\Extension\Translate;
use Bone\Log\LogPackage;
use Bone\Router\PlatesStrategy;
use Bone\Router\Router;
use Bone\Router\RouterPackage;
use Bone\View\ViewPackage;
use Bone\Test\Router\AnotherFakeRequestHandler;
use Bone\Test\Router\FakeController;
use Bone\Test\Router\FakeMiddleware;
use Bone\Test\Router\FakePackage\FakePackagePackage;
use Bone\Test\Router\FakeRequestHandler;
use Bone\Test\Router\MiddlewareTestHandler;
use Bone\Test\Router\RouterHandler;
use Codeception\TestCase\Test;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\Uri;
use Laminas\I18n\Translator\Loader\Gettext;
use Laminas\I18n\Translator\Translator;
use League\Route\Http\Exception\MethodNotAllowedException;
use League\Route\Http\Exception\NotFoundException;
use League\Route\Route;
use Locale;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class RouterTest extends Test
{
    /** @var Container */
    protected $container;

    protected function _before()
    {
        $this->container = $c = new Container();
        $this->container['viewFolder'] = 'tests/_data';
        $this->container['default_layout'] = 'whatever';
        $this->container['error_pages'] = [
            'exception' => 'error::error',
            401 => 'error::not-authorised',
            403 => 'error::not-found',
            405 => 'error::not-allowed',
            500 => 'error::error',
        ];
        $router = new Router();
        $this->container[Router::class] = $router;
        $package = new ViewPackage();
        $package->addToContainer($this->container);
        $package = new RouterPackage();
        $package->addToContainer($this->container);
    }

    protected function _after()
    {
        unset($this->container);
    }

    public function testRouter()
    {
        $router = $this->container->get(Router::class);
        $router->map('GET', '/whatever', [RouterHandler::class, 'handle']);
        $routes = $router->getRoutes();
        $this->assertCount(1, $routes);
        $router->removeRoute($routes[0]);
        $routes = $router->getRoutes();
        $this->assertCount(0, $routes);
        $router->map('GET', '/whatever', [RouterHandler::class, 'handle']);
        $request = new ServerRequest([], [], '/whatever');
        $response = $router->handle($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testJson()
    {
        $router = $this->container->get(Router::class);
        $router->map('GET', '/json', [RouterHandler::class, 'json']);
        $request = new ServerRequest([], [], '/json');
        /** @var ResponseInterface $response */
        $response = $router->handle($request);
        $response->getBody()->rewind();
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals('{"pong":"2020-04-06"}', $response->getBody()->getContents());
    }



    public function test500()
    {
        $router = $this->container->get(Router::class);
        $router->map('GET', '/death-by-exception', [RouterHandler::class, 'explode']);
        $request = new ServerRequest([], [], '/death-by-exception');
        $this->expectException(Exception::class);
        $router->handle($request);
    }


    public function test403()
    {
        $router = $this->container->get(Router::class);
        $router->map('GET', '/denied', [RouterHandler::class, 'deny']);
        $request = new ServerRequest([], [], '/denied');
        $this->expectException(Exception::class);
        $this->expectExceptionCode(403);
        $router->handle($request);
    }



    public function test405()
    {
        $router = $this->container->get(Router::class);
        $router->map('GET', '/json', [RouterHandler::class, 'json']);
        $request = new ServerRequest([], [], '/json', 'POST');
        $this->expectException(MethodNotAllowedException::class);
        $router->handle($request);
    }

    public function test404()
    {
        $this->expectException(NotFoundException::class);
        $request = new ServerRequest([], [], '/lost');
        $router = $this->container->get(Router::class);
        $router->handle($request);
    }
}
