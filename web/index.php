<?php

use Brave\CoreConnector\Bootstrap;
use Psr\Container\ContainerExceptionInterface;

require_once(__DIR__ . '/../vendor/autoload.php');

define('ROOT_DIR', realpath(__DIR__ . '/../'));

$bootstrap = new Bootstrap();
try {
    $bootstrap->run();
} catch (ContainerExceptionInterface $e) {
    error_log((string)$e);
    echo 'Error 500';
}
