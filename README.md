# Neucore connector boilerplate example 

## Install

- Install dependencies with `composer install`.
- Copy `.env.dist` to `.env` and adjust values or set the corresponding environment variables in another way.
- Add any URL you need in `config/routes.php`.
- If you need groups from Brave Core to secure routes, see `Bootstrap::enableRoutes()`,
enable the appropriate middlewares and configure your roles in `config/security.php`.

See https://www.slimframework.com/docs/v4/start/web-servers.html for how to set up a web server.

### Docker (dev)

```shell
# build
docker-compose build

# start
docker-compose up

# enter PHP shell
docker-compose exec boilerplate_php /bin/sh

# show PHP logs
docker logs -f --details boilerplate_php
```

## Changelog

### 4.0.0

- Raised minimum PHP version to 7.3.
- PHP 8 compatibility.
- Replaced bravecollective/sso-basics with tkhamez/eve-sso.

### 3.0.0

Preconfigured for
- EVE SSO v2
- Slim 4 with slim/psr7, php-di
- Added .env file for configuration variables instead of config.php

Needs PHP >= 7.2

### 2.0.0

Preconfigured for
- EVE SSO v2
- Slim 3

Needs PHP >= 7.1

### 1.0.0

Preconfigured for
- EVE SSO v1
- Slim 3

Needs PHP >= 5.5
