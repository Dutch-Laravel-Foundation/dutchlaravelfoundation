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
