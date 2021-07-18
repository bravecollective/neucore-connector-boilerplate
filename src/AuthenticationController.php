<?php

namespace Brave\CoreConnector;

use Brave\Sso\Basics\SessionHandlerInterface;
use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthenticationController extends \Brave\Sso\Basics\AuthenticationController
{
    /**
     * @var RoleProvider|null
     */
    private $roleProvider;

    /**
     * @var SessionHandlerInterface|mixed 
     */
    private $sessionHandler;

    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
        $this->roleProvider = $container->get(RoleProvider::class);
        $this->sessionHandler = $this->container->get(SessionHandlerInterface::class);
    }

    /**
     * EVE SSO callback.
     * 
     * @param bool $ssoV2
     * @throws Exception
     */
    public function auth(ServerRequestInterface $request, ResponseInterface $response, $ssoV2 = false): ResponseInterface
    {
        #parent::auth($request, $response); // SSO v1
        parent::auth($request, $response, true); // SSO v2
        $this->roleProvider->clear();

        return $response->withHeader('Location', '/');
    }
    
    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->sessionHandler->set('eveAuth', null);
        $this->roleProvider->clear();
        
        return $response->withHeader('Location', '/');
    }
}
