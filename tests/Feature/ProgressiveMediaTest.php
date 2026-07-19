<?php

declare(strict_types=1);

namespace Tests\Feature;

use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tests\TestCase;

class ProgressiveMediaTest extends TestCase
{
    public function testSharedPartialOwnsTheRepeatedImageAttributes(): void
    {
        $partialPath = resource_path('views/partials/_progressive_media_attributes.antlers.html');
        $partial = file_get_contents($partialPath);

        $this->assertNotFalse($partial);
        $this->assertStringContainsString('width="{{ width }}"', $partial);
        $this->assertStringContainsString('height="{{ height }}"', $partial);
        $this->assertStringContainsString('loading="{{ loading ?? \'lazy\' }}"', $partial);
        $this->assertStringContainsString('decoding="async"', $partial);
        $this->assertStringContainsString('data-progressive-media', $partial);
        $this->assertStringContainsString('data-media-state="loading"', $partial);
        $this->assertStringContainsString('performance.getEntriesByName(this.currentSrc)', $partial);
        $this->assertStringContainsString('new window.URL(this.currentSrc,location.href)', $partial);
        $this->assertStringContainsString("this.dataset.mediaCached=''", $partial);
        $this->assertStringContainsString("this.dataset.mediaState='loaded'", $partial);

        foreach ($this->antlersTemplates() as $path) {
            if ($path === $partialPath) {
                continue;
            }

            $template = file_get_contents($path);

            $this->assertNotFalse($template);
            $this->assertStringNotContainsString('data-media-state="loading"', $template, $path);
        }
    }

    public function testProgressiveMediaFramesUseAWhiteStripedBackground(): void
    {
        $stylesheet = file_get_contents(resource_path('css/progressive-media.css'));

        $this->assertNotFalse($stylesheet);
        $this->assertStringContainsString('background-color: #fff;', $stylesheet);
        $this->assertStringContainsString('repeating-linear-gradient', $stylesheet);
        $this->assertStringContainsString('--progressive-media-opacity-duration: 0ms;', $stylesheet);
    }

    public function testInlineArticleImagesDoNotExposeTheirProgressiveFrame(): void
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

    public function testArticleRailsKeepPageSpacingSeparateFromProseSpacing(): void
    {
        $stylesheet = file_get_contents(resource_path('css/redesign-editorial.css'));

        $this->assertNotFalse($stylesheet);
        $this->assertMatchesRegularExpression(
            '/\.editorial-rail\s*\{[^}]*padding-bottom:\s*10rem;/s',
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

    public function testArticleTocKeepsSpaceBelowTheDynamicHeader(): void
    {
        $stylesheet = file_get_contents(resource_path('css/redesign-editorial.css'));

        $this->assertNotFalse($stylesheet);
        $this->assertMatchesRegularExpression(
            '/\.editorial-toc\s*\{[^}]*top:\s*calc\(var\(--dlf-header-visible-height,\s*0px\) \+ 1\.5rem\);/s',
            $stylesheet,
        );
    }

    public function testLarafestArticleUsesLevelTwoSectionHeadingsForTheTableOfContents(): void
    {
        $xpath = $this->pageXPath('/nieuws/larafest-2026-security-platforms-en-escape-boxes-aan-zee');
        $headings = $xpath->query('//article[contains(concat(" ", normalize-space(@class), " "), " editorial-article__prose ")]//h2');

        $this->assertInstanceOf(DOMNodeList::class, $headings);
        $this->assertCount(3, $headings);
        $this->assertSame('Worms, packages en Shai-Hulud', trim($headings->item(0)?->textContent ?? ''));
        $this->assertSame('Praktijkverhalen uit echte platformen', trim($headings->item(1)?->textContent ?? ''));
        $this->assertSame('Eten, escape boxes en bijpraten', trim($headings->item(2)?->textContent ?? ''));
    }

    public function testTabletArticleHeroUsesTheTallerImageAndArticleCopyWidth(): void
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

    public function testEmbleArticleDoesNotContainManualBreakNodes(): void
    {
        $article = file_get_contents(base_path('content/collections/insights/2026-04-13-2200.emble-ontwikkelaars-pur-sang-blijven-zich-door-ontwikkelen.md'));

        $this->assertNotFalse($article);
        $this->assertStringNotContainsString('type: hardBreak', $article);
    }

    public function testNewsAndKnowledgeArticlesDoNotContainManualBreaks(): void
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

    public function testArticleProseHeadingsUseNormalWeightIncludingBoldContent(): void
    {
        $stylesheet = file_get_contents(resource_path('css/redesign-editorial.css'));

        $this->assertNotFalse($stylesheet);
        $this->assertMatchesRegularExpression(
            '/\.editorial-article \.editorial-article__prose :is\(h1, h2, h3, h4, h5, h6\):not\(\.dlf-block \*\)\s*\{[^}]*font-weight:\s*400;/s',
            $stylesheet,
        );
        $this->assertMatchesRegularExpression(
            '/\.editorial-article \.editorial-article__prose :is\(h1, h2, h3, h4, h5, h6\):not\(\.dlf-block \*\) :is\(strong, b\)\s*\{[^}]*font-weight:\s*inherit;/s',
            $stylesheet,
        );
    }

    public function testNewsAndKnowledgeArticleHeadingsDoNotContainBoldMarks(): void
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

    public function testAboutPageMarksOnlySubstantialContentMedia(): void
    {
        $xpath = $this->pageXPath('/over-ons');
        $images = $this->progressiveImages($xpath);

        $this->assertGreaterThanOrEqual(12, $images->length);

        foreach ($images as $image) {
            $this->assertProgressiveImageContract($image, '/over-ons');
            $this->assertSame('lazy', $image->getAttribute('loading'));
        }
    }

    public function testHomepageUsesEagerLoadingOnlyForItsPrimaryPhoto(): void
    {
        $xpath = $this->pageXPath('/');
        $primary = $xpath->query('//img[@data-progressive-media and @fetchpriority="high"]');
        $lazy = $xpath->query('//img[@data-progressive-media and @loading="lazy"]');

        $this->assertInstanceOf(DOMNodeList::class, $primary);
        $this->assertInstanceOf(DOMNodeList::class, $lazy);
        $this->assertCount(1, $primary);
        $this->assertGreaterThan(0, $lazy->length);
        $this->assertSame('eager', $primary->item(0)?->attributes?->getNamedItem('loading')?->nodeValue);
    }

    public function testPublicPageFamiliesExposeStableProgressiveMedia(): void
    {
        $uris = [
            '/',
            '/aanbestedingen',
            '/agenda',
            '/cases',
            '/een-eigen-systeem-laten-bouwen-is-betaalbaarder-dan-je-denkt',
            '/kennis',
            '/larabelles',
            '/laravel-het-framework-dat-jouw-systeem-op-maat-tot-een-succes-maakt',
            '/lid-worden',
            '/nieuws',
            '/over-ons',
            '/podcast',
        ];

        foreach ($uris as $uri) {
            $xpath = $this->pageXPath($uri);
            $images = $this->progressiveImages($xpath);

            $this->assertGreaterThan(0, $images->length, $uri);

            foreach ($images as $image) {
                $this->assertProgressiveImageContract($image, $uri);
            }
        }
    }

    public function testHeaderFooterIconsAndLogosAreNotProgressiveMedia(): void
    {
        $xpath = $this->pageXPath('/over-ons');
        $images = $xpath->query('//header//img | //footer//img');

        $this->assertInstanceOf(DOMNodeList::class, $images);
        $this->assertGreaterThan(0, $images->length);

        foreach ($images as $image) {
            $this->assertInstanceOf(DOMElement::class, $image);
            $this->assertFalse($image->hasAttribute('data-progressive-media'));
        }
    }

    public function testDesktopFooterBrandDividerSpansTheFullViewport(): void
    {
        $stylesheet = file_get_contents(resource_path('css/redesign-shell.css'));

        $this->assertNotFalse($stylesheet);
        $this->assertMatchesRegularExpression(
            '/@media \(min-width:\s*1024px\)\s*\{.*?\.dlf-footer-brand\s*\{[^}]*margin-inline:\s*calc\(50% - 50vw\);[^}]*padding-inline:\s*calc\(50vw - 50%\);/s',
            $stylesheet,
        );
    }

    public function testInlineArticlePhotographyUsesTheProgressiveMediaContract(): void
    {
        $uris = [
            '/kennis/ai-gedreven-zoekfunctionaliteit-dankzij-vragenai',
            '/kennis/graphql-met-laravel-en-lighthouse',
            '/nieuws/dlf-meetup-bij-dij',
        ];

        foreach ($uris as $uri) {
            $xpath = $this->pageXPath($uri);
            $images = $xpath->query('//article[contains(concat(" ", normalize-space(@class), " "), " editorial-article__prose ")]//img[contains(@src, ".gif") or contains(@src, ".jpg") or contains(@src, ".jpeg") or contains(@src, ".png") or contains(@src, ".webp")]');

            $this->assertInstanceOf(DOMNodeList::class, $images);
            $this->assertGreaterThan(0, $images->length, $uri);

            foreach ($images as $image) {
                $this->assertProgressiveImageContract($image, $uri);
            }
        }
    }

    private function assertProgressiveImageContract(DOMElement $image, string $context): void
    {
        $this->assertSame('loading', $image->getAttribute('data-media-state'), $context);
        $this->assertContains($image->getAttribute('loading'), ['eager', 'lazy'], $context);
        $this->assertSame('async', $image->getAttribute('decoding'), $context);
        $this->assertStringContainsString('performance.getEntriesByName(this.currentSrc)', $image->getAttribute('onload'), $context);
        $this->assertStringContainsString('new window.URL(this.currentSrc,location.href)', $image->getAttribute('onload'), $context);
        $this->assertStringContainsString("this.dataset.mediaCached=''", $image->getAttribute('onload'), $context);
        $this->assertStringContainsString("this.dataset.mediaState='loaded'", $image->getAttribute('onload'), $context);
        $this->assertMatchesRegularExpression('/^[1-9][0-9]*$/', $image->getAttribute('width'), $context);
        $this->assertMatchesRegularExpression('/^[1-9][0-9]*$/', $image->getAttribute('height'), $context);
        $this->assertInstanceOf(DOMElement::class, $image->parentNode, $context);

        $frame = $image->parentNode;

        while ($frame instanceof DOMElement && ! $frame->hasAttribute('data-progressive-media-frame')) {
            $frame = $frame->parentNode;
        }

        $this->assertInstanceOf(
            DOMElement::class,
            $frame,
            "{$context}\n{$image->getAttribute('src')}",
        );
    }

    /** @return DOMNodeList<DOMElement> */
    private function progressiveImages(DOMXPath $xpath): DOMNodeList
    {
        $images = $xpath->query('//img[@data-progressive-media]');

        $this->assertInstanceOf(DOMNodeList::class, $images);

        return $images;
    }

    private function pageXPath(string $uri): DOMXPath
    {
        $response = $this->get($uri);
        $this->assertSame(200, $response->getStatusCode(), "{$uri}\n{$response->getContent()}");

        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML($response->getContent());
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }

    /** @return list<string> */
    private function antlersTemplates(): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(resource_path('views')),
        );
        $paths = [];

        foreach ($iterator as $file) {
            if (! $file instanceof SplFileInfo || ! $file->isFile()) {
                continue;
            }

            if (! str_ends_with($file->getFilename(), '.antlers.html')) {
                continue;
            }

            $paths[] = $file->getPathname();
        }

        sort($paths);

        return $paths;
    }
}
