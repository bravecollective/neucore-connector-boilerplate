<?php

namespace Brave\CoreConnector;

use Brave\Sso\Basics\EveAuthentication;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SlimSession\Helper;

class HomeController
{
    /**
     * @var EveAuthentication|null
     */
    private $eveAuth;

    /**
     * @var RoleProvider
     */
    private $roleProvider;

    public function __construct(ContainerInterface $container) {
        $sessionHandler = $container->get(Helper::class);
        $this->eveAuth = $sessionHandler->get('eveAuth');
        $this->roleProvider = $container->get(RoleProvider::class);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $response->getBody()->write(str_replace(
            ['{{name}}', '{{roles}}'],
            [
                $this->eveAuth ? $this->eveAuth->getCharacterName() : '',
                implode(', ', $this->roleProvider->getCachedRoles())
            ],
            '7o {{name}} <br>
                (roles: {{roles}})<br>
                <br>
                <a href="/login">login</a><br>
                <a href="/secured">secured</a> (only works if middleware is enabled in Bootstrap class)<br>
                <a href="/logout">logout</a>'
        ));

        return $response;
    }
}
