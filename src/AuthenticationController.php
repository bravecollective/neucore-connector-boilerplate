<?php

namespace Brave\CoreConnector;

use Eve\Sso\AuthenticationProvider;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SlimSession\Helper;
use UnexpectedValueException;

class AuthenticationController
{
    private RoleProvider $roleProvider;

    private Helper $sessionHandler;

    private mixed $settings;

    private AuthenticationProvider $authProvider;

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __construct(ContainerInterface $container) {
        $this->roleProvider = $container->get(RoleProvider::class);
        $this->sessionHandler = $container->get(Helper::class);
        $this->settings = $container->get('settings');
        $this->authProvider = $container->get(AuthenticationProvider::class);
    }

    /**
     * Show the login page.
     *
     * @throws Exception
     * @noinspection PhpUnusedParameterInspection
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $serviceName = $this->settings['brave.serviceName'] ?? 'Brave Service';
        $state = $this->authProvider->generateState();
        $this->sessionHandler->set('ssoState', $state);

        $loginUrl = $this->authProvider->buildLoginUrl($state);

        $templateCode = file_get_contents(__DIR__ . '/../html/sso_page.html');

        $body = str_replace([
            '{{serviceName}}',
            '{{loginUrl}}'
        ], [
            $serviceName,
            $loginUrl
        ], $templateCode);

        $response->getBody()->write($body);

        return $response;
    }

    /**
     * EVE SSO callback.
     * 
     * @throws Exception
     */
    public function auth(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $queryParameters = $request->getQueryParams();
        $sessionState = $this->sessionHandler->get('ssoState');

        if (empty($queryParameters['code']) || empty($queryParameters['state']) || empty($sessionState)) {
            $response->getBody()->write('Invalid SSO state, please try again.');
            return $response;
        }

        $state = $queryParameters['state'];
        $code = $queryParameters['code'];

        try {
            $eveAuth = $this->authProvider->validateAuthenticationV2($state, $sessionState, $code);
        } catch(UnexpectedValueException $e) {
            $response->getBody()->write($e->getMessage());
            return $response;
        }

        $this->sessionHandler->set('eveAuth', $eveAuth);
        $this->roleProvider->clearCache();

        return $response->withHeader('Location', '/');
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->sessionHandler->set('eveAuth', null);
        $this->roleProvider->clearCache();
        
        return $response->withHeader('Location', '/');
    }
}
