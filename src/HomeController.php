<?php

namespace Brave\CoreConnector;

use Eve\Sso\EveAuthentication;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SlimSession\Helper;

class HomeController
{
    private ?EveAuthentication $eveAuth;

    private RoleProvider $roleProvider;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(ContainerInterface $container) {
        $sessionHandler = $container->get(Helper::class);
        $this->eveAuth = $sessionHandler->get('eveAuth');
        $this->roleProvider = $container->get(RoleProvider::class);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $templateCode = file_get_contents(__DIR__ . '/../html/index.html');
        $body = str_replace(
            ['{{name}}', '{{roles}}'],
            [
                htmlspecialchars($this->eveAuth ? $this->eveAuth->getCharacterName() : '(not logged in)'),
                htmlspecialchars(implode(', ', $this->roleProvider->getCachedRoles()))
            ],
            $templateCode
        );
        $response->getBody()->write($body);

        return $response;
    }
}
