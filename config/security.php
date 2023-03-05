<?php
/**
 * Required roles (one of them) for routes.
 *
 * First route match will be used, matched by "starts-with"
 */
return [
    '/secured' => ['core:admin', 'core:group2'],
];
