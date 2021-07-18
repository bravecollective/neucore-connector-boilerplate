<?php

namespace Brave\CoreConnector;

use Brave\Sso\Basics\AuthenticationProvider;
use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SlimSession\Helper;

class AuthenticationController
{
    /**
     * @var RoleProvider
     */
    private $roleProvider;

    /**
     * @var Helper
     */
    private $sessionHandler;

    /**
     * @var mixed
     */
    private $settings;

    /**
     * @var AuthenticationProvider
     */
    private $authProvider;

    public function __construct(ContainerInterface $container) {
        $this->roleProvider = $container->get(RoleProvider::class);
        $this->sessionHandler = $container->get(Helper::class);
        $this->settings = $container->get('settings');
        $this->authProvider = $container->get(AuthenticationProvider::class);;
    }

    /**
     * Show the login page.
     *
     * @throws Exception
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

        if (!isset($queryParameters['code']) || !isset($queryParameters['state'])) {
            $response->getBody()->write('Invalid SSO state, please try again.');
            return $response;
        }

        $state = $queryParameters['state'];
        $code = $queryParameters['code'];
        $sessionState = $this->sessionHandler->get('ssoState');

        try {
            #$eveAuth = $this->authProvider->validateAuthentication($state, $sessionState, $code); // SSO v1
            $eveAuth = $this->authProvider->validateAuthenticationV2($state, $sessionState, $code); // SSO v2
        } catch(\UnexpectedValueException $e) {
            $response->getBody()->write($e->getMessage());
            return $response;
        }

        $this->sessionHandler->set('eveAuth', $eveAuth);
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
