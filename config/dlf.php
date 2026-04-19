<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | llms.txt configuration
    |--------------------------------------------------------------------------
    |
    | Controls generation of /llms.txt and /llms-full.txt. The preamble is
    | hand-written prose describing the foundation; `highlighted_pages` is a
    | curated list rendered above the auto-generated collection listings.
    |
    */

    'llms' => [
        'preamble' => 'The Dutch Laravel Foundation (Stichting Dutch Laravel Foundation) is a non-profit that promotes the adoption and professional use of the Laravel PHP framework in the Netherlands. We connect members, host events, share knowledge, and support students via internship placements.',

        'highlighted_pages' => [
            ['label' => 'Over ons', 'slug' => 'over-ons'],
            ['label' => 'Lid worden', 'slug' => 'lid-worden'],
            ['label' => 'What is Laravel', 'slug' => 'what-is-laravel'],
        ],

        // Max entries per auto section in llms.txt
        'max_entries_per_section' => 50,
    ],

];
