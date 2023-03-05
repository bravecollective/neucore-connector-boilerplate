<?php

use Brave\CoreConnector\AuthenticationController;
use Brave\CoreConnector\HomeController;
use Slim\Psr7\Factory\ResponseFactory;

return [
    '/' => ['GET' => HomeController::class],

    '/secured' => ['GET' => function () {
        $response = (new ResponseFactory())->createResponse();
        $response->getBody()->write('secured<br><a href="/">back</a>');
        return $response;
    }],

    // SSO via eve-sso package
    '/login'  => ['GET' => [AuthenticationController::class, 'index']],
    '/auth'   => ['GET' => [AuthenticationController::class, 'auth']],
    '/logout' => ['GET' => [AuthenticationController::class, 'logout']],
];
