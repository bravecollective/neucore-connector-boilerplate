<?php

use Brave\CoreConnector\RoleProvider;
use Brave\NeucoreApi\Api\ApplicationGroupsApi;
use Eve\Sso\AuthenticationProvider;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ResponseFactory;
use SlimSession\Helper;

return [
    'settings' => require_once('config.php'),

    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);
        return AppFactory::create();
    },

    ResponseFactoryInterface::class => function () {
        return new ResponseFactory();
    },

    AuthenticationProvider::class => function (ContainerInterface $container) {
        $settings = $container->get('settings');

        return new AuthenticationProvider(
            [
                'clientId' => $settings['SSO_CLIENT_ID'],
                'clientSecret' => $settings['SSO_CLIENT_SECRET'],
                'redirectUri' => $settings['SSO_REDIRECT_URI'],
                'urlAuthorize' => $settings['SSO_URL_AUTHORIZE'],
                'urlAccessToken' => $settings['SSO_URL_ACCESS_TOKEN'],
                'urlResourceOwnerDetails' => $settings['SSO_URL_RESOURCE_OWNER_DETAILS'],
                'urlKeySet' => $settings['SSO_URL_JWT_KEY_SET'],
                'urlRevoke' => $settings['SSO_URL_REVOKE_URL'],
            ],
            explode(' ', $settings['SSO_SCOPES']),
        );
    },

    Helper::class => function () {
        return new Helper();
    },

    ApplicationGroupsApi::class => function (ContainerInterface $container) {
        $apiKey = base64_encode(
            $container->get('settings')['CORE_APP_ID'] .
            ':'.
            $container->get('settings')['CORE_APP_SECRET']
        );
        $config = Brave\NeucoreApi\Configuration::getDefaultConfiguration();
        $config->setHost($container->get('settings')['CORE_URL']);
        $config->setAccessToken($apiKey);
        return new ApplicationGroupsApi(null, $config);
    },

    RoleProvider::class => function (ContainerInterface $container) {
        return new RoleProvider(
            $container->get(ApplicationGroupsApi::class),
            $container->get(Helper::class)
        );
    }
];
