<?php

$appDomain = env('APP_DOMAIN', 'saas.com.br');

return [
    'domain' => $appDomain,
    'local_default_subdomain' => env('TENANCY_LOCAL_DEFAULT_SUBDOMAIN', 'cliente1'),
    'central_domains' => array_values(array_unique(array_filter([
        $appDomain,
        'www.'.$appDomain,
        'api.'.$appDomain,
        'localhost',
        '127.0.0.1',
    ]))),
];