<?php

declare(strict_types=1);
use App\Content\PublicPages\PublicPageDataMapper;
use App\Data\PublicPages\ContentBlockData;
use App\Data\PublicPages\PublicPageData;

it('maps a complete page without leaking graphql response arrays', function () {
    $page = (new PublicPageDataMapper)->map(publicPageResponse());

    expect($page)->toBeInstanceOf(PublicPageData::class);
    expect($page->template)->toBe('templates/default');
    expect($page->menuTheme)->toBe('white');
    expect($page->headerContentHtml)->toBe('<p>Header copy</p>');
    expect($page->seo->title)->toBe('SEO title');
    expect($page->callToAction?->title)->toBe('Join us');
    expect($page->support->memberCount)->toBe(74);
    expect($page->support->board[0]->name)->toBe('Ada');
    expect($page->support->foundingPartners[0]->city)->toBe('Amsterdam');
    expect($page->support->generalLandingCases[0]->slug)->toBe('dropday');
    expect($page->support->frameworkLandingCases[0]->slug)->toBe('diabetes-nl-helpt-je-verder-weten-delen-doen');

    $this->assertContainsOnlyInstancesOf(ContentBlockData::class, $page->content);
    expect($page->content[0]->html)->toBe('<h2>Rendered heading</h2>');
    expect($page->content[1]->asset?->url)->toBe('/assets/hero.jpg');
    expect($page->content[2]->headingHtml)->toBe('<h2>Two columns</h2>');
    expect($page->content[2]->left[0]->html)->toBe('<p>Left body</p>');
    expect($page->content[3]->primaryAction?->label)->toBe('Get started');
    expect($page->content[4]->features[0]->bodyHtml)->toBe('<p>Rendered markdown</p>');
    expect($page->content[5]->cards[0]->heading)->toBe('Card one');
    expect($page->content[6]->stats[0]->value)->toBe('74+');
    expect($page->content[7]->attributionName)->toBe('Taylor');
    expect($page->content[8]->logos[0]->name)->toBe('Partner');
    expect($page->content[9]->plans[0]->featured)->toBeTrue();
    expect($page->content[10]->tone)->toBe('dark');
});
it('returns null when the graphql entry is not a page', function () {
    expect((new PublicPageDataMapper)->map(['page' => null]))->toBeNull();
    expect((new PublicPageDataMapper)->map(['page' => ['__typename' => 'Entry_Insights_Insights']]))->toBeNull();
});
/** @return array<string, mixed> */
function publicPageResponse(): array
{
    return [
        'page' => [
            '__typename' => 'Entry_Pages_Pages',
            'id' => 'page-id',
            'title' => 'Public page',
            'slug' => 'public-page',
            'url' => '/public-page',
            'uri' => '/public-page',
            'template' => 'templates/default',
            'menu_color' => ['value' => 'white', 'label' => 'Wit'],
            'header_title' => 'Header title',
            'header_content' => '<p>Header copy</p>',
            'meta_title' => 'SEO title',
            'meta_description' => 'SEO description',
            'meta_keywords' => 'laravel,php',
            'call_to_action' => [
                '__typename' => 'Entry_Cta_Cta',
                'id' => 'cta-id',
                'title' => 'Join us',
                'description' => '<p>Become a member</p>',
                'eyebrow' => 'Community',
                'benefits' => ['Knowledge', 'Network'],
                'link' => ['url' => '/word-lid', 'title' => 'Join'],
                'link_2' => null,
                'theme' => ['value' => 'red', 'label' => 'Rood'],
                'button_text' => 'Word lid',
                'button_style' => ['value' => 'primary', 'label' => 'Primair'],
                'button_text_2' => null,
                'button_style_2' => null,
            ],
            'content' => [
                ['__typename' => 'BardText', 'type' => 'text', 'text' => '<h2>Rendered heading</h2>'],
                ['__typename' => 'Set_Content_Image', 'id' => 'image', 'type' => 'image', 'image' => publicPageAsset('hero.jpg')],
                [
                    '__typename' => 'Set_Content_DoubleColumn',
                    'id' => 'columns',
                    'type' => 'double_column',
                    'heading' => '<h2>Two columns</h2>',
                    'left' => [['__typename' => 'BardText', 'type' => 'text', 'text' => '<p>Left body</p>']],
                    'right' => [[
                        '__typename' => 'Set_Content_Right_MetaBlock',
                        'id' => 'meta',
                        'type' => 'meta_block',
                        'title' => '74+',
                        'content' => 'members',
                    ]],
                ],
                [
                    '__typename' => 'Set_Content_DlfHero',
                    'id' => 'hero',
                    'type' => 'dlf_hero',
                    'eyebrow' => 'Laravel',
                    'heading' => 'Build better',
                    'heading_level' => ['value' => 'h1', 'label' => 'H1'],
                    'body' => '<p>Hero body</p>',
                    'image' => publicPageAsset('hero.jpg'),
                    'primary_label' => 'Get started',
                    'primary_link' => ['url' => '/start', 'title' => null],
                    'secondary_label' => 'Learn more',
                    'secondary_link' => ['url' => '/learn', 'title' => null],
                    'image_position' => ['value' => 'right', 'label' => 'Rechts'],
                ],
                [
                    '__typename' => 'Set_Content_DlfFeatureGrid',
                    'id' => 'features',
                    'type' => 'dlf_feature_grid',
                    'eyebrow' => null,
                    'heading' => 'Benefits',
                    'introduction' => '<p>Introduction</p>',
                    'columns' => ['value' => '3', 'label' => 'Drie'],
                    'features' => [[
                        'id' => 'feature',
                        'icon' => publicPageAsset('icon.svg'),
                        'heading' => 'Secure',
                        'body' => '<p>Rendered markdown</p>',
                        'link_label' => 'Read',
                        'link' => ['url' => '/read', 'title' => null],
                    ]],
                ],
                [
                    '__typename' => 'Set_Content_DlfCardGrid',
                    'id' => 'cards',
                    'type' => 'dlf_card_grid',
                    'heading' => 'Cases',
                    'cards' => [[
                        'id' => 'card',
                        'image' => publicPageAsset('card.jpg'),
                        'eyebrow' => 'Case',
                        'heading' => 'Card one',
                        'body' => '<p>Rendered card</p>',
                        'link_label' => 'View',
                        'link' => ['url' => '/case', 'title' => null],
                    ]],
                ],
                [
                    '__typename' => 'Set_Content_DlfStats',
                    'id' => 'stats',
                    'type' => 'dlf_stats',
                    'heading' => 'Numbers',
                    'stats' => [['id' => 'stat', 'value' => '74+', 'label' => 'Members', 'context' => 'Across NL']],
                ],
                [
                    '__typename' => 'Set_Content_DlfQuote',
                    'id' => 'quote',
                    'type' => 'dlf_quote',
                    'quote' => 'Elegant applications',
                    'name' => 'Taylor',
                    'role' => 'Creator',
                    'image' => publicPageAsset('taylor.jpg'),
                    'tone' => ['value' => 'soft', 'label' => 'Lichtgrijs'],
                ],
                [
                    '__typename' => 'Set_Content_DlfLogoCloud',
                    'id' => 'logos',
                    'type' => 'dlf_logo_cloud',
                    'heading' => 'Partners',
                    'logos' => [[
                        'id' => 'logo',
                        'logo' => publicPageAsset('partner.svg'),
                        'name' => 'Partner',
                        'link' => ['url' => 'https://partner.test', 'title' => null],
                    ]],
                ],
                [
                    '__typename' => 'Set_Content_DlfPricing',
                    'id' => 'pricing',
                    'type' => 'dlf_pricing',
                    'heading' => 'Plans',
                    'plans' => [[
                        'id' => 'plan',
                        'name' => 'Member',
                        'price' => '€ 100',
                        'suffix' => 'per year',
                        'description' => '<p>For agencies</p>',
                        'features' => [['id' => 'plan-feature', 'feature' => 'Events']],
                        'button_label' => 'Join',
                        'button_link' => ['url' => '/join', 'title' => null],
                        'featured' => true,
                    ]],
                ],
                [
                    '__typename' => 'Set_Content_DlfCtaPanel',
                    'id' => 'panel',
                    'type' => 'dlf_cta_panel',
                    'heading' => 'Ready?',
                    'tone' => ['value' => 'dark', 'label' => 'Donker'],
                ],
            ],
        ],
        'members' => ['total' => 74, 'data' => []],
        'board' => ['data' => [[
            'id' => 'board-id',
            'title' => 'Ada',
            'function' => 'Chair',
            'photo' => publicPageAsset('ada.jpg'),
        ]]],
        'foundingPartners' => ['data' => [[
            'id' => 'member-id',
            'title' => 'Agency',
            'slug' => 'agency',
            'url' => '/leden/agency',
            'city' => 'Amsterdam',
            'province' => ['value' => 'noord-holland', 'label' => 'Noord-Holland'],
            'logo' => publicPageAsset('agency.svg'),
        ]]],
        'landingCases' => ['data' => [
            publicPageCase('platform-voor-recycling-printercartridges'),
            publicPageCase('dropday'),
            publicPageCase('diabetes-nl-helpt-je-verder-weten-delen-doen'),
        ]],
    ];
}
/** @return array<string, mixed> */
function publicPageAsset(string $path): array
{
    return [
        'id' => "assets::{$path}",
        'url' => "/assets/{$path}",
        'permalink' => "https://example.test/assets/{$path}",
        'path' => $path,
        'extension' => pathinfo($path, PATHINFO_EXTENSION),
        'width' => 1200,
        'height' => 800,
        'focus_css' => '50% 50%',
        'alt' => 'Alternative text',
    ];
}
/** @return array<string, mixed> */
function publicPageCase(string $slug): array
{
    return [
        'id' => "case-{$slug}",
        'title' => ucfirst($slug),
        'title_long' => "Long {$slug}",
        'slug' => $slug,
        'url' => "/cases/{$slug}",
        'introduction' => '<p>Case introduction</p>',
        'featured_image' => publicPageAsset("{$slug}.jpg"),
    ];
}
