<?php

namespace Brave\CoreConnector;

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Middleware\Session;

class Bootstrap
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        if (is_readable(ROOT_DIR . '/.env')) {
            $dotEnv = new Dotenv(ROOT_DIR);
            $dotEnv->load();
        }

        $builder = new ContainerBuilder();
        $builder->addDefinitions(require_once(ROOT_DIR . '/config/container.php'));
        $this->container = $builder->build();
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function enableRoutes(): App
    {
        /** @var App $app */
        $routesConfigurator = require_once(ROOT_DIR . '/config/routes.php');
        $app = $routesConfigurator($this->container);

        // uncomment this if you need groups from Neucore to secure routes
        /*
         $app->add(new \Tkhamez\Slim\RoleAuth\SecureRouteMiddleware(
            $this->container->get(\Psr\Http\Message\ResponseFactoryInterface::class), 
            include ROOT_DIR . '/config/security.php')
        );
        $app->add(new \Tkhamez\Slim\RoleAuth\RoleMiddleware($this->container->get(RoleProvider::class)));
        */
        
        $app->add(new Session([
            'name' => 'brave_service',
            'autorefresh' => true,
            'lifetime' => '1 hour'
        ]));

        // Add routing middleware last, so the `route` attribute from `$request` is available
        $app->addRoutingMiddleware();
        
        return $app;
    }
}
