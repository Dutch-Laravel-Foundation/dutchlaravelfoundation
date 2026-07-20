<?php

declare(strict_types=1);

use App\ContentSecurityPolicy;
use App\Support\ViteNonceGenerator;

return [
    'presets' => [
        ContentSecurityPolicy::class,
    ],

    'directives' => [],
    'report_only_presets' => [],
    'report_only_directives' => [],
    'report_uri' => env('CSP_REPORT_URI', ''),
    'report_only_uri' => '',
    'report_to' => '',
    'report_only_to' => '',
    'reporting_endpoints' => [],
    'enabled' => env('CSP_ENABLED', true),
    'enabled_while_hot_reloading' => true,
    'nonce_generator' => ViteNonceGenerator::class,
    'nonce_enabled' => true,
];
