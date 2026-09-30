<?php

declare(strict_types=1);

namespace Tests\Unit\Content\SiteShell;

use App\Content\SiteShell\SiteShellDataMapper;
use App\Data\SiteShell\SiteShellData;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SiteShellDataMapperTest extends TestCase
{
    #[Test]
    public function it_maps_the_complete_site_shell_and_marks_current_navigation_ancestors(): void
    {
        $shell = (new SiteShellDataMapper)->map($this->response(), '/wat-is-laravel/');

        $this->assertInstanceOf(SiteShellData::class, $shell);
        $this->assertSame([
            'organization' => [
                'title' => 'Dutch',
                'address' => 'Edelgasstraat 103',
                'zipcode' => '2718 TE',
                'city' => 'Zoetermeer',
                'phone' => '+31 (0)88 73 33 319',
                'email' => 'info@dutchlaravelfoundation.nl',
                'coc' => '75104512',
                'logo' => [
                    'id' => 'globals::LaravelBrandMark.svg',
                    'url' => '/assets/globals/LaravelBrandMark.svg',
                    'permalink' => 'https://example.test/assets/globals/LaravelBrandMark.svg',
                    'width' => 562.0,
                    'height' => 236.0,
                ],
                'site' => [
                    'handle' => 'default',
                    'name' => 'Dutch',
                    'locale' => 'nl_NL',
                    'shortLocale' => 'nl',
                    'url' => 'https://example.test/',
                ],
            ],
            'seo' => [
                'title' => 'Dutch Laravel Foundation',
                'description' => 'De kennis- en brancheorganisatie voor Laravel developers',
                'keywords' => 'Laravel, PHP',
            ],
            'openGraph' => [
                'image' => [
                    'id' => 'globals::social-card.png',
                    'url' => '/assets/globals/social-card.png',
                    'permalink' => 'https://example.test/assets/globals/social-card.png',
                    'width' => 1200.0,
                    'height' => 630.0,
                ],
            ],
            'navigation' => [
                'main' => [
                    [
                        'id' => 'laravel',
                        'title' => 'Laravel',
                        'slug' => null,
                        'url' => '#',
                        'permalink' => null,
                        'isCurrent' => false,
                        'isAncestor' => true,
                        'children' => [
                            [
                                'id' => 'what-is-laravel',
                                'title' => 'Wat is Laravel?',
                                'slug' => 'wat-is-laravel',
                                'url' => '/wat-is-laravel',
                                'permalink' => 'https://example.test/wat-is-laravel',
                                'isCurrent' => true,
                                'isAncestor' => false,
                                'children' => [],
                            ],
                        ],
                    ],
                    [
                        'id' => 'news',
                        'title' => 'Nieuws',
                        'slug' => 'nieuws',
                        'url' => '/nieuws',
                        'permalink' => 'https://example.test/nieuws',
                        'isCurrent' => false,
                        'isAncestor' => false,
                        'children' => [],
                    ],
                ],
                'legal' => [
                    [
                        'id' => 'privacy-nav',
                        'title' => 'Privacy statement',
                        'slug' => 'privacy-statement',
                        'url' => '/privacy-statement',
                        'permalink' => 'https://example.test/privacy-statement',
                        'isCurrent' => false,
                        'isAncestor' => false,
                        'children' => [],
                    ],
                ],
            ],
            'footer' => [
                'members' => [[
                    'id' => 'member-1',
                    'title' => 'Acme Laravel',
                    'slug' => 'acme-laravel',
                    'url' => '/leden/acme-laravel',
                ]],
                'socials' => [[
                    'id' => 'linkedin',
                    'title' => 'LinkedIn',
                    'link' => [
                        'url' => 'https://www.linkedin.com/company/dutch-laravel-foundation',
                        'title' => 'Volg ons op LinkedIn',
                    ],
                    'icon' => [
                        'id' => 'socials::linkedin.svg',
                        'url' => '/assets/socials/linkedin.svg',
                        'permalink' => 'https://example.test/assets/socials/linkedin.svg',
                        'width' => 24.0,
                        'height' => 24.0,
                    ],
                ]],
            ],
            'defaultCta' => [
                'id' => 'ee5d33de-9a24-4860-92dd-3503740b62af',
                'title' => 'Sluit je aan',
                'description' => '<p>Bouw mee aan de Laravel-community.</p>',
                'eyebrow' => 'Samen verder',
                'benefits' => ['Kennis delen', 'Netwerk uitbreiden'],
                'link' => ['url' => '/lid-worden', 'title' => 'Lid worden'],
                'secondaryLink' => ['url' => '/contact', 'title' => 'Neem contact op'],
                'theme' => ['value' => 'dark', 'label' => 'Donker'],
                'buttonStyle' => ['value' => 'primary', 'label' => 'Primair'],
                'secondaryButtonStyle' => ['value' => 'light', 'label' => 'Licht'],
                'buttonText' => 'Lid worden',
                'secondaryButtonText' => 'Contact',
            ],
            'newsletter' => [
                'handle' => 'newsletter',
                'title' => 'Nieuwsbrief',
                'honeypot' => 'fax_number',
                'rules' => ['email' => ['required', 'email']],
                'fields' => [[
                    'handle' => 'email',
                    'type' => 'text',
                    'display' => 'E-mailadres',
                    'instructions' => 'Vul je e-mailadres in.',
                    'width' => 100,
                    'ifConditions' => [],
                    'unlessConditions' => [],
                    'config' => [
                        'input_type' => 'email',
                        'placeholder' => 'jij@example.nl',
                    ],
                ]],
            ],
        ], $shell->toArray());
    }

    #[Test]
    public function it_normalizes_absolute_navigation_urls_and_empty_optional_values(): void
    {
        $response = $this->response();
        $response['mainNavigation']['tree'][1]['page']['url'] = 'https://example.test/nieuws/?from=menu';
        $response['mainNavigation']['tree'][1]['page']['permalink'] = '';
        $response['defaultCta'] = null;
        $response['newsletter']['fields'][0]['instructions'] = '';

        $shell = (new SiteShellDataMapper)->map($response, '/nieuws?ref=header');

        $this->assertTrue($shell->navigation->main[1]->isCurrent);
        $this->assertNull($shell->navigation->main[1]->permalink);
        $this->assertNull($shell->defaultCta);
        $this->assertNull($shell->newsletter->fields[0]->instructions);
    }

    /** @return array<string, mixed> */
    private function response(): array
    {
        return [
            'organization' => [
                'title' => 'Dutch Laravel Foundation',
                'address' => 'Edelgasstraat 103',
                'zipcode' => '2718 TE',
                'city' => 'Zoetermeer',
                'phone' => '+31 (0)88 73 33 319',
                'email' => 'info@dutchlaravelfoundation.nl',
                'coc' => '75104512',
                'logo' => [
                    'id' => 'globals::LaravelBrandMark.svg',
                    'url' => '/assets/globals/LaravelBrandMark.svg',
                    'permalink' => 'https://example.test/assets/globals/LaravelBrandMark.svg',
                    'width' => 562,
                    'height' => 236,
                ],
                'site' => [
                    'handle' => 'default',
                    'name' => 'Dutch',
                    'locale' => 'nl_NL',
                    'short_locale' => 'nl',
                    'url' => 'https://example.test/',
                ],
            ],
            'seo' => [
                'meta_title' => 'Dutch Laravel Foundation',
                'meta_description' => 'De kennis- en brancheorganisatie voor Laravel developers',
                'meta_keywords' => 'Laravel, PHP',
            ],
            'openGraph' => [
                'opengraph_image' => [
                    'id' => 'globals::social-card.png',
                    'url' => '/assets/globals/social-card.png',
                    'permalink' => 'https://example.test/assets/globals/social-card.png',
                    'width' => 1200,
                    'height' => 630,
                ],
            ],
            'mainNavigation' => [
                'tree' => [
                    [
                        'page' => [
                            'id' => 'laravel',
                            'title' => 'Laravel',
                            'url' => '#',
                            'permalink' => null,
                        ],
                        'children' => [[
                            'page' => [
                                'id' => 'what-is-laravel',
                                'title' => 'Wat is Laravel?',
                                'slug' => 'wat-is-laravel',
                                'url' => '/wat-is-laravel',
                                'permalink' => 'https://example.test/wat-is-laravel',
                            ],
                            'children' => [],
                        ]],
                    ],
                    [
                        'page' => [
                            'id' => 'news',
                            'title' => 'Nieuws',
                            'slug' => 'nieuws',
                            'url' => '/nieuws',
                            'permalink' => 'https://example.test/nieuws',
                        ],
                        'children' => [],
                    ],
                ],
            ],
            'legalNavigation' => [
                'tree' => [[
                    'page' => [
                        'id' => 'privacy-nav',
                        'title' => 'Privacy statement',
                        'url' => null,
                        'permalink' => null,
                        'page' => [
                            'id' => 'privacy',
                            'title' => 'Privacy',
                            'slug' => 'privacy-statement',
                            'url' => '/privacy-statement',
                            'permalink' => 'https://example.test/privacy-statement',
                        ],
                    ],
                    'children' => [],
                ]],
            ],
            'members' => [
                'data' => [[
                    'id' => 'member-1',
                    'title' => 'Acme Laravel',
                    'slug' => 'acme-laravel',
                    'url' => '/leden/acme-laravel',
                ]],
            ],
            'socials' => [
                'data' => [[
                    'id' => 'linkedin',
                    'title' => 'LinkedIn',
                    'link' => [
                        'url' => 'https://www.linkedin.com/company/dutch-laravel-foundation',
                        'title' => 'Volg ons op LinkedIn',
                    ],
                    'icon' => [
                        'id' => 'socials::linkedin.svg',
                        'url' => '/assets/socials/linkedin.svg',
                        'permalink' => 'https://example.test/assets/socials/linkedin.svg',
                        'width' => 24,
                        'height' => 24,
                    ],
                ]],
            ],
            'defaultCta' => [
                'id' => 'ee5d33de-9a24-4860-92dd-3503740b62af',
                'title' => 'Sluit je aan',
                'description' => '<p>Bouw mee aan de Laravel-community.</p>',
                'eyebrow' => 'Samen verder',
                'benefits' => ['Kennis delen', 'Netwerk uitbreiden'],
                'link' => ['url' => '/lid-worden', 'title' => 'Lid worden'],
                'link_2' => ['url' => '/contact', 'title' => 'Neem contact op'],
                'theme' => ['value' => 'dark', 'label' => 'Donker'],
                'button_style' => ['value' => 'primary', 'label' => 'Primair'],
                'button_style_2' => ['value' => 'light', 'label' => 'Licht'],
                'button_text' => 'Lid worden',
                'button_text_2' => 'Contact',
            ],
            'newsletter' => [
                'handle' => 'newsletter',
                'title' => 'Nieuwsbrief',
                'honeypot' => 'fax_number',
                'rules' => ['email' => ['required', 'email']],
                'fields' => [[
                    'handle' => 'email',
                    'type' => 'text',
                    'display' => 'E-mailadres',
                    'instructions' => 'Vul je e-mailadres in.',
                    'width' => 100,
                    'if' => [],
                    'unless' => [],
                    'config' => [
                        'input_type' => 'email',
                        'placeholder' => 'jij@example.nl',
                    ],
                ]],
            ],
        ];
    }
}
