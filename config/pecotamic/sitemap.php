<?php

return [
    'url' => 'sitemap.xml',
    'expire' => 60,
    'include_entries' => true,
    'include_terms' => true,
    'include_collection_terms' => true,
    'entry_types' => null,

    'exclude_urls' => [
        '#^/newsletter/?$#',
        '#^/aanvraag/bedankt/?$#',
        '#^/terms-and-conditions/?$#',
    ],

    'filter' => null,
    'properties' => null,
];
