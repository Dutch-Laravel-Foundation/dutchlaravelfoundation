<?php

declare(strict_types=1);

namespace Tests\Unit\Content;

use App\Content\Mappers\HomePageDataMapper;
use App\Data\Pages\HomePageData;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class HomePageDataMapperTest extends TestCase
{
    #[Test]
    public function it_maps_the_statamic_home_query_to_a_cms_neutral_dto(): void
    {
        $mapper = new HomePageDataMapper;

        $page = $mapper->map([
            'entry' => [
                'id' => 'home',
                'title' => 'Home',
                'slug' => 'home',
                'uri' => '/',
                'header_title' => 'Voor Laravel developers, door Laravel developers.',
                'header_content' => '<p>Wij versterken het Nederlandse Laravel-ecosysteem.</p>',
                'menu_color' => ['value' => 'dark', 'label' => 'Donker'],
                'meta_title' => 'Dutch Laravel Foundation',
                'meta_description' => 'De Nederlandse Laravel community.',
                'meta_keywords' => 'laravel, php',
                'call_to_action' => [
                    'id' => 'cta-1',
                    'title' => 'Doe mee',
                    'description' => '<p>Sluit je aan.</p>',
                    'eyebrow' => 'Community',
                    'benefits' => ['Kennis delen'],
                    'link' => ['url' => '/lid-worden', 'title' => 'Lid worden'],
                    'link_2' => null,
                    'theme' => ['value' => 'dark', 'label' => 'Donker'],
                    'button_text' => 'Lid worden',
                    'button_style' => ['value' => 'primary', 'label' => 'Primair'],
                    'button_text_2' => null,
                    'button_style_2' => null,
                ],
            ],
        ]);

        $this->assertInstanceOf(HomePageData::class, $page);
        $this->assertSame([
            'id' => 'home',
            'title' => 'Home',
            'slug' => 'home',
            'uri' => '/',
            'headerTitle' => 'Voor Laravel developers, door Laravel developers.',
            'headerContent' => '<p>Wij versterken het Nederlandse Laravel-ecosysteem.</p>',
            'menuTheme' => 'dark',
            'footerCta' => [
                'id' => 'cta-1',
                'title' => 'Doe mee',
                'description' => '<p>Sluit je aan.</p>',
                'eyebrow' => 'Community',
                'benefits' => ['Kennis delen'],
                'link' => ['url' => '/lid-worden', 'title' => 'Lid worden'],
                'secondaryLink' => null,
                'theme' => ['value' => 'dark', 'label' => 'Donker'],
                'buttonStyle' => ['value' => 'primary', 'label' => 'Primair'],
                'secondaryButtonStyle' => null,
                'buttonText' => 'Lid worden',
                'secondaryButtonText' => null,
            ],
            'seo' => [
                'title' => 'Dutch Laravel Foundation',
                'description' => 'De Nederlandse Laravel community.',
                'keywords' => 'laravel, php',
            ],
        ], $page->toArray());
    }
}
