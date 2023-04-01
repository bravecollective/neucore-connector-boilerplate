<?php

namespace Brave\CoreConnector;

use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Dotenv\Dotenv;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Middleware\Session;
use Tkhamez\Slim\RoleAuth\RoleMiddleware;
use Tkhamez\Slim\RoleAuth\SecureRouteMiddleware;

class Bootstrap
{
    private ContainerInterface $container;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        if (is_readable(ROOT_DIR . '/.env')) {
            $dotEnv = Dotenv::createUnsafeImmutable(ROOT_DIR);
            $dotEnv->load();
        }

        $config = include ROOT_DIR . '/config/config.php';
        $containerDefinition = Container::getDefinition($config);

        $builder = new ContainerBuilder();
        $builder->addDefinitions($containerDefinition);
        $this->container = $builder->build();
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function run(): void
    {
        $app = $this->container->get(App::class);
        $this->addRoutes($app);
        $this->addMiddleware($app);
        $app->run();
    }

    private function addRoutes(App $app): void
    {
        $routes = include ROOT_DIR . '/config/routes.php';

        foreach ($routes as $pattern => $config) {
            foreach ($config as $method => $callable) {
                switch ($method) {
                    case 'GET':
                        $app->get($pattern, $callable);
                        break;
                    case 'POST':
                        $app->post($pattern, $callable);
                        break;
                    case 'DELETE':
                        $app->delete($pattern, $callable);
                        break;
                    case 'PUT':
                        $app->put($pattern, $callable);
                        break;
                }
            }
        }
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     */
    private function addMiddleware(App $app): void
    {
        $app->add(new SecureRouteMiddleware(
            $this->container->get(ResponseFactoryInterface::class),
            include ROOT_DIR . '/config/security.php',
            ['redirect_url' => '/login']
        ));
        $app->add(new RoleMiddleware($this->container->get(RoleProvider::class)));

        $app->add(new Session([
            'name' => 'brave_service',
            'autorefresh' => true,
            'lifetime' => '1 hour'
        ]));

        // Add routing middleware last, so the `route` attribute from `$request` is available
        $app->addRoutingMiddleware();
    }
}
