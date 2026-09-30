<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class ProgressiveMediaTest extends TestCase
{
    public function test_progressive_media_frames_use_a_white_striped_background(): void
    {
        $stylesheet = file_get_contents(resource_path('css/progressive-media.css'));

        $this->assertNotFalse($stylesheet);
        $this->assertStringContainsString('background-color: #fff;', $stylesheet);
        $this->assertStringContainsString('repeating-linear-gradient', $stylesheet);
        $this->assertStringContainsString('--progressive-media-opacity-duration: 0ms;', $stylesheet);
    }

    public function test_inline_article_images_do_not_expose_their_progressive_frame(): void
    {
        $stylesheet = file_get_contents(resource_path('css/redesign-editorial.css'));

        $this->assertNotFalse($stylesheet);
        $this->assertMatchesRegularExpression(
            '/\.editorial-article \.editorial-article__prose \.dlf-inline-progressive-media\s*\{[^}]*margin-block:\s*1\.375rem;/s',
            $stylesheet,
        );
        $this->assertMatchesRegularExpression(
            '/\.editorial-article \.editorial-article__prose \.dlf-inline-progressive-media > img\s*\{[^}]*margin-block:\s*0;/s',
            $stylesheet,
        );
    }

    public function test_article_rails_keep_page_spacing_separate_from_prose_spacing(): void
    {
        $stylesheet = file_get_contents(resource_path('css/redesign-editorial.css'));

        $this->assertNotFalse($stylesheet);
        $this->assertMatchesRegularExpression(
            '/\.editorial-rail\s*\{[^}]*padding-bottom:\s*var\(--dlf-footer-cta-stage-padding,\s*10rem\);/s',
            $stylesheet,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.editorial-rail--article\s*\{[^}]*padding-bottom:\s*0;/s',
            $stylesheet,
        );
        $this->assertMatchesRegularExpression(
            '/\.editorial-article__body\s*\{[^}]*padding:\s*4rem 2\.5rem 5rem;/s',
            $stylesheet,
        );
        $this->assertMatchesRegularExpression(
            '/\.editorial-article \.editorial-article__prose > :last-child:not\(\.dlf-block\) > :last-child\s*\{[^}]*margin-bottom:\s*0;/s',
            $stylesheet,
        );
    }

    public function test_article_toc_keeps_space_below_the_dynamic_header(): void
    {
        $stylesheet = file_get_contents(resource_path('css/redesign-editorial.css'));

        $this->assertNotFalse($stylesheet);
        $this->assertMatchesRegularExpression(
            '/\.editorial-toc\s*\{[^}]*top:\s*calc\(var\(--dlf-header-visible-height,\s*0px\) \+ 1\.5rem\);/s',
            $stylesheet,
        );
    }

    public function test_larafest_article_uses_level_two_section_headings_for_the_table_of_contents(): void
    {
        $response = $this->withHeaders($this->inertiaHeaders())
            ->get('/nieuws/larafest-2026-security-platforms-en-escape-boxes-aan-zee');
        $blocks = $response->json('props.editorial.content');

        $response->assertOk()->assertHeader('X-Inertia', 'true');
        $this->assertIsArray($blocks);

        $html = collect($blocks)->pluck('html')->filter()->implode('');
        preg_match_all('/<h2\b[^>]*>(.*?)<\/h2>/s', $html, $headings);

        $this->assertSame([
            'Worms, packages en Shai-Hulud',
            'Praktijkverhalen uit echte platformen',
            'Eten, escape boxes en bijpraten',
        ], array_map(static fn (string $heading): string => trim(strip_tags($heading)), $headings[1]));
    }

    public function test_tablet_article_hero_uses_the_taller_image_and_article_copy_width(): void
    {
        $stylesheet = file_get_contents(resource_path('css/redesign-editorial.css'));

        $this->assertNotFalse($stylesheet);
        $this->assertMatchesRegularExpression(
            '/@media \(min-width:\s*640px\) and \(max-width:\s*1023px\)\s*\{.*?\.editorial-article__figure\s*\{[^}]*min-height:\s*22\.5rem;/s',
            $stylesheet,
        );
        $this->assertMatchesRegularExpression(
            '/@media \(min-width:\s*640px\) and \(max-width:\s*1023px\)\s*\{.*?\.editorial-article__head > \*\s*\{[^}]*max-width:\s*38rem;[^}]*margin-inline:\s*auto;/s',
            $stylesheet,
        );
        $this->assertMatchesRegularExpression(
            '/@media \(min-width:\s*640px\) and \(max-width:\s*1023px\)\s*\{.*?\.editorial-article__head\s*\{[^}]*align-items:\s*center;/s',
            $stylesheet,
        );
    }

    public function test_emble_article_does_not_contain_manual_break_nodes(): void
    {
        $article = file_get_contents(base_path('content/collections/insights/2026-04-13-2200.emble-ontwikkelaars-pur-sang-blijven-zich-door-ontwikkelen.md'));

        $this->assertNotFalse($article);
        $this->assertStringNotContainsString('type: hardBreak', $article);
    }

    public function test_news_and_knowledge_articles_do_not_contain_manual_breaks(): void
    {
        foreach (['insights', 'knowledge'] as $collection) {
            $paths = glob(base_path("content/collections/{$collection}/*.md"));

            $this->assertIsArray($paths);

            foreach ($paths as $path) {
                $article = file_get_contents($path);

                $this->assertNotFalse($article);
                $this->assertDoesNotMatchRegularExpression('/type:\s*hard_?break|<br\s*\/?\s*>/i', $article, $path);
            }
        }
    }

    public function test_article_prose_headings_use_normal_weight_including_bold_content(): void
    {
        $stylesheet = file_get_contents(resource_path('css/redesign-editorial.css'));

        $this->assertNotFalse($stylesheet);
        $this->assertMatchesRegularExpression(
            '/\.editorial-article \.editorial-article__prose :is\(h1, h2, h3, h4, h5, h6\):not\(\.dlf-block \*\)\s*\{[^}]*font-weight:\s*400;/s',
            $stylesheet,
        );
        $this->assertMatchesRegularExpression(
            '/\.editorial-article\s+\.editorial-article__prose\s+:is\(h1, h2, h3, h4, h5, h6\):not\(\.dlf-block \*\)\s+:is\(strong, b\)\s*\{[^}]*font-weight:\s*inherit;/s',
            $stylesheet,
        );
    }

    public function test_news_and_knowledge_article_headings_do_not_contain_bold_marks(): void
    {
        foreach (['insights', 'knowledge'] as $collection) {
            $paths = glob(base_path("content/collections/{$collection}/*.md"));

            $this->assertIsArray($paths);

            foreach ($paths as $path) {
                $article = file_get_contents($path);

                $this->assertNotFalse($article);

                preg_match_all(
                    '/^  -\n    type: heading\n(?:(?!^  -\n).)*/ms',
                    $article,
                    $headings,
                );

                foreach ($headings[0] as $heading) {
                    $this->assertStringNotContainsString('type: bold', $heading, $path);
                }
            }
        }
    }

    public function test_about_page_marks_only_substantial_content_media(): void
    {
        $response = $this->withHeaders($this->inertiaHeaders())->get('/over-ons');
        $component = file_get_contents(resource_path('js/pages/PublicPages/About.tsx'));

        $response->assertOk()->assertJsonPath('component', 'PublicPages/About');
        $this->assertNotFalse($component);
        $this->assertSame(3, substr_count($component, '<ProgressiveImage'));
        $this->assertSame(3, substr_count($component, 'data-progressive-media-frame'));
        $this->assertSame(3, substr_count($component, 'decoding="async"'));
    }

    public function test_homepage_uses_eager_loading_only_for_its_primary_photo(): void
    {
        $hero = file_get_contents(resource_path('js/components/home/HomeHero.tsx'));
        $community = file_get_contents(resource_path('js/components/home/CurrentCommunity.tsx'));

        $this->assertNotFalse($hero);
        $this->assertNotFalse($community);
        $this->assertSame(1, substr_count($hero, 'fetchPriority="high"'));
        $this->assertSame(1, substr_count($hero, 'loading="eager"'));
        $this->assertStringContainsString('loading="lazy"', $community);
    }

    public function test_public_page_families_expose_stable_progressive_media(): void
    {
        $components = [
            resource_path('js/components/home/ProgressiveImage.tsx'),
            resource_path('js/components/editorial-react/ProgressiveImage.tsx'),
            resource_path('js/components/public-pages-react/ProgressiveImage.tsx'),
        ];

        foreach ($components as $path) {
            $component = file_get_contents($path);

            $this->assertNotFalse($component);
            $this->assertStringContainsString('data-progressive-media', $component, $path);
            $this->assertStringContainsString('data-media-state={mediaState}', $component, $path);
            $this->assertStringContainsString('onError={handleError}', $component, $path);
            $this->assertStringContainsString('onLoad={handleLoad}', $component, $path);
        }
    }

    public function test_header_footer_icons_and_logos_are_not_progressive_media(): void
    {
        $header = file_get_contents(resource_path('js/components/site/Header.tsx'));
        $footer = file_get_contents(resource_path('js/components/site/Footer.tsx'));

        $this->assertNotFalse($header);
        $this->assertNotFalse($footer);
        $this->assertStringContainsString('<img', $header);
        $this->assertStringContainsString('<img', $footer);
        $this->assertStringNotContainsString('ProgressiveImage', $header);
        $this->assertStringNotContainsString('ProgressiveImage', $footer);
    }

    public function test_desktop_footer_brand_divider_spans_the_full_viewport(): void
    {
        $stylesheet = file_get_contents(resource_path('css/redesign-shell.css'));

        $this->assertNotFalse($stylesheet);
        $this->assertMatchesRegularExpression(
            '/@media \(min-width:\s*1024px\)\s*\{.*?\.dlf-footer-brand\s*\{[^}]*margin-inline:\s*calc\(50% - 50vw\);[^}]*padding-inline:\s*calc\(50vw - 50%\);/s',
            $stylesheet,
        );
    }

    public function test_mobile_footer_copyright_is_centered(): void
    {
        $stylesheet = file_get_contents(resource_path('css/redesign-shell.css'));

        $this->assertNotFalse($stylesheet);
        $this->assertMatchesRegularExpression(
            '/@media \(max-width:\s*639px\)\s*\{.*?\.dlf-footer-bottom\s*>\s*p\s*\{[^}]*text-align:\s*center;/s',
            $stylesheet,
        );
    }

    public function test_inline_article_photography_is_preserved_in_the_inertia_dto(): void
    {
        $uris = [
            '/kennis/ai-gedreven-zoekfunctionaliteit-dankzij-vragenai',
            '/kennis/graphql-met-laravel-en-lighthouse',
            '/nieuws/dlf-meetup-bij-dij',
        ];

        foreach ($uris as $uri) {
            $response = $this->withHeaders($this->inertiaHeaders())->get($uri);
            $editorial = $response->json('props.editorial');

            $response->assertOk()->assertHeader('X-Inertia', 'true');
            $this->assertIsArray($editorial);
            $this->assertStringContainsString('<img', json_encode($editorial, JSON_THROW_ON_ERROR), $uri);
        }
    }

    /** @return array<string, string> */
    private function inertiaHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'X-Inertia' => 'true',
            'X-Inertia-Version' => hash_file('xxh128', public_path('build/manifest.json')),
        ];
    }
}
