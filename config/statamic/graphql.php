<?php

return [

    /*
    |--------------------------------------------------------------------------
    | GraphQL
    |--------------------------------------------------------------------------
    |
    | Here you may enable the GraphQL API, and select which resources
    | are available to be queried, depending on your site's needs.
    |
    | https://statamic.dev/graphql
    |
    */

    'enabled' => env('STATAMIC_GRAPHQL_ENABLED', false),

    'resources' => [
        'collections' => [
            'authors',
            'board',
            'cases' => [
                'allowed_filters' => ['member', 'slug'],
            ],
            'clients' => [
                'allowed_filters' => ['slug'],
            ],
            'cta',
            'events' => [
                'allowed_filters' => ['date_start'],
            ],
            'insights' => [
                'allowed_filters' => ['category', 'highlight'],
            ],
            'internships' => [
                'allowed_filters' => ['member'],
            ],
            'knowledge' => [
                'allowed_filters' => ['category', 'highlight'],
            ],
            'members' => [
                'allowed_filters' => ['founding_partner'],
            ],
            'pages',
            'partners',
            'podcasts',
            'reviews',
            'socials',
        ],
        'navs' => ['legal', 'main'],
        'taxonomies' => false,
        'assets' => [
            'assets',
            'board',
            'cases',
            'clients',
            'events',
            'globals',
            'insights',
            'knowledge',
            'members',
            'socials',
        ],
        'globals' => ['dlf', 'opengraph', 'seo'],
        'forms' => ['newsletter', 'contact', 'become_member', 'sales_funnel'],
        'sites' => true,
        'users' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | By default, Statamic will cache each request until the specified
    | expiry, or until content is changed. See the documentation for
    | more details on how to customize your cache implementation.
    |
    | https://statamic.dev/graphql#caching
    |
    */

    'cache' => [
        'expiry' => 60,
    ],

];
