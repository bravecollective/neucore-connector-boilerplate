<?php

return [
    // SSO CONFIGURATION
    'SSO_CLIENT_ID' => getenv('SSO_CLIENT_ID'),
    'SSO_CLIENT_SECRET' => getenv('SSO_CLIENT_SECRET'),
    'SSO_REDIRECT_URI' => getenv('SSO_REDIRECT_URI'),
    'SSO_URL_AUTHORIZE' => 'https://login.eveonline.com/v2/oauth/authorize',
    'SSO_URL_ACCESS_TOKEN' => 'https://login.eveonline.com/v2/oauth/token',
    'SSO_URL_RESOURCE_OWNER_DETAILS' => '', // only for SSO v1
    'SSO_URL_JWT_KEY_SET' => 'https://login.eveonline.com/oauth/jwks',
    'SSO_URL_REVOKE_URL' => 'https://login.eveonline.com/v2/oauth/revoke',
    'SSO_SCOPES' => '',

    // App
    'brave.serviceName' => getenv('BRAVE_SERVICENAME'),

    // NEUCORE
    'CORE_URL' => getenv('CORE_URL'),
    'CORE_APP_ID' => getenv('CORE_APP_ID'),
    'CORE_APP_SECRET' => getenv('CORE_APP_SECRET'),
];
