<?php

declare(strict_types=1);

$isProduction = env('APP_ENV') === 'production';

return [
    'service' => 'Turnstile', // options: Recaptcha / Hcaptcha / Turnstile / Altcha
    'sitekey' => env('CAPTCHA_SITEKEY') ?: ($isProduction ? '' : '1x00000000000000000000AA'),
    'secret' => env('CAPTCHA_SECRET') ?: ($isProduction ? '' : '1x0000000000000000000000000000000AA'),
    'collections' => [],
    'forms' => 'all',
    'user_login' => true,
    'user_registration' => true,
    'disclaimer' => '',
    'invisible' => false,
    'hide_badge' => true,
    'enable_api_routes' => false,
    'custom_should_verify' => null,
];
