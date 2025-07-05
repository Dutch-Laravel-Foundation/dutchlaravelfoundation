<?php

return [
    'service' => 'Turnstile', // options: Recaptcha / Hcaptcha / Turnstile / Altcha
    'sitekey' => env('CAPTCHA_SITEKEY', ''),
    'secret' => env('CAPTCHA_SECRET', ''),
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
