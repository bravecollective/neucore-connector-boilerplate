<?php

use Brave\CoreConnector\RoleProvider;
use Brave\NeucoreApi\Api\ApplicationGroupsApi;
use Eve\Sso\AuthenticationProvider;
use League\OAuth2\Client\Provider\GenericProvider;
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

    GenericProvider::class => function (ContainerInterface $container) {
        $settings = $container->get('settings');

        return new GenericProvider([
            'clientId' => $settings['SSO_CLIENT_ID'],
            'clientSecret' => $settings['SSO_CLIENT_SECRET'],
            'redirectUri' => $settings['SSO_REDIRECTURI'],
            'urlAuthorize' => $settings['SSO_URL_AUTHORIZE'],
            'urlAccessToken' => $settings['SSO_URL_ACCESSTOKEN'],
            'urlResourceOwnerDetails' => $settings['SSO_URL_RESOURCEOWNERDETAILS'],
        ]);
    },

    AuthenticationProvider::class => function (ContainerInterface $container) {
        $settings = $container->get('settings');

        return new AuthenticationProvider(
            $container->get(GenericProvider::class),
            explode(' ', $settings['SSO_SCOPES']),
            $settings['SSO_URL_JWT_KEY_SET']
        );
    },

    Helper::class => function () {
        return new Helper();
    },

    ApplicationGroupsApi::class => function (ContainerInterface $container) {
        $apiKey = base64_encode(
            $container->get('settings')['CORE_APP_ID'] .
            ':'.
            $container->get('settings')['CORE_APP_TOKEN']
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
