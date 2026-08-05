<?php

declare(strict_types=1);

use Tests\TestCase;

it('shared partial owns the repeated image attributes', function () {
    $partialPath = resource_path('views/partials/_progressive_media_attributes.antlers.html');
    $partial = file_get_contents($partialPath);

    $this->assertNotFalse($partial);
    $this->assertStringContainsString('width="{{ width }}"', $partial);
    $this->assertStringContainsString('height="{{ height }}"', $partial);
    $this->assertStringContainsString('loading="{{ loading ?? \'lazy\' }}"', $partial);
    $this->assertStringContainsString('decoding="async"', $partial);
    $this->assertStringContainsString('data-progressive-media', $partial);
    $this->assertStringContainsString('data-media-state="loading"', $partial);
    $this->assertStringNotContainsString('onload=', $partial);

    foreach (antlersTemplates() as $path) {
        if ($path === $partialPath) {
            continue;
        }

        $template = file_get_contents($path);

        $this->assertNotFalse($template);
        $this->assertStringNotContainsString('data-media-state="loading"', $template, $path);
    }
});
it('progressive media frames use a white striped background', function () {
    $stylesheet = file_get_contents(resource_path('css/progressive-media.css'));

    $this->assertNotFalse($stylesheet);
    $this->assertStringContainsString('background-color: #fff;', $stylesheet);
    $this->assertStringContainsString('repeating-linear-gradient', $stylesheet);
    $this->assertStringContainsString('--progressive-media-opacity-duration: 0ms;', $stylesheet);
});
it('inline article images do not expose their progressive frame', function () {
    $stylesheet = file_get_contents(resource_path('css/redesign-editorial.css'));

    $this->assertNotFalse($stylesheet);
    expect($stylesheet)->toMatch('/\.editorial-article \.editorial-article__prose \.dlf-inline-progressive-media\s*\{[^}]*margin-block:\s*1\.375rem;/s');
    expect($stylesheet)->toMatch('/\.editorial-article \.editorial-article__prose \.dlf-inline-progressive-media > img\s*\{[^}]*margin-block:\s*0;/s');
});
it('article rails keep page spacing separate from prose spacing', function () {
    $stylesheet = file_get_contents(resource_path('css/redesign-editorial.css'));

    $this->assertNotFalse($stylesheet);
    expect($stylesheet)->toMatch('/\.editorial-rail\s*\{[^}]*padding-bottom:\s*var\(--dlf-footer-cta-stage-padding,\s*10rem\);/s');
    $this->assertDoesNotMatchRegularExpression(
        '/\.editorial-rail--article\s*\{[^}]*padding-bottom:\s*0;/s',
        $stylesheet,
    );
    expect($stylesheet)->toMatch('/\.editorial-article__body\s*\{[^}]*padding:\s*4rem 2\.5rem 5rem;/s');
    expect($stylesheet)->toMatch('/\.editorial-article \.editorial-article__prose > :last-child:not\(\.dlf-block\) > :last-child\s*\{[^}]*margin-bottom:\s*0;/s');
});
it('article toc keeps space below the dynamic header', function () {
    $stylesheet = file_get_contents(resource_path('css/redesign-editorial.css'));

    $this->assertNotFalse($stylesheet);
    expect($stylesheet)->toMatch('/\.editorial-toc\s*\{[^}]*top:\s*calc\(var\(--dlf-header-visible-height,\s*0px\) \+ 1\.5rem\);/s');
});
it('larafest article uses level two section headings for the table of contents', function () {
    $xpath = progressivePageXPath($this, '/nieuws/larafest-2026-security-platforms-en-escape-boxes-aan-zee');
    $headings = $xpath->query('//article[contains(concat(" ", normalize-space(@class), " "), " editorial-article__prose ")]//h2');

    expect($headings)->toBeInstanceOf(DOMNodeList::class);
    expect($headings)->toHaveCount(3);
    expect(trim($headings->item(0)?->textContent ?? ''))->toBe('Worms, packages en Shai-Hulud');
    expect(trim($headings->item(1)?->textContent ?? ''))->toBe('Praktijkverhalen uit echte platformen');
    expect(trim($headings->item(2)?->textContent ?? ''))->toBe('Eten, escape boxes en bijpraten');
});
it('tablet article hero uses the taller image and article copy width', function () {
    $stylesheet = file_get_contents(resource_path('css/redesign-editorial.css'));

    $this->assertNotFalse($stylesheet);
    expect($stylesheet)->toMatch('/@media \(min-width:\s*640px\) and \(max-width:\s*1023px\)\s*\{.*?\.editorial-article__figure\s*\{[^}]*min-height:\s*22\.5rem;/s');
    expect($stylesheet)->toMatch('/@media \(min-width:\s*640px\) and \(max-width:\s*1023px\)\s*\{.*?\.editorial-article__head > \*\s*\{[^}]*max-width:\s*38rem;[^}]*margin-inline:\s*auto;/s');
    expect($stylesheet)->toMatch('/@media \(min-width:\s*640px\) and \(max-width:\s*1023px\)\s*\{.*?\.editorial-article__head\s*\{[^}]*align-items:\s*center;/s');
});
it('emble article does not contain manual break nodes', function () {
    $article = file_get_contents(base_path('content/collections/insights/2026-04-13-2200.emble-ontwikkelaars-pur-sang-blijven-zich-door-ontwikkelen.md'));

    $this->assertNotFalse($article);
    $this->assertStringNotContainsString('type: hardBreak', $article);
});
it('news and knowledge articles do not contain manual breaks', function () {
    foreach (['insights', 'knowledge'] as $collection) {
        $paths = glob(base_path("content/collections/{$collection}/*.md"));

        expect($paths)->toBeArray();

        foreach ($paths as $path) {
            $article = file_get_contents($path);

            $this->assertNotFalse($article);
            $this->assertDoesNotMatchRegularExpression('/type:\s*hard_?break|<br\s*\/?\s*>/i', $article, $path);
        }
    }
});
it('article prose headings use normal weight including bold content', function () {
    $stylesheet = file_get_contents(resource_path('css/redesign-editorial.css'));

    $this->assertNotFalse($stylesheet);
    expect($stylesheet)->toMatch('/\.editorial-article \.editorial-article__prose :is\(h1, h2, h3, h4, h5, h6\):not\(\.dlf-block \*\)\s*\{[^}]*font-weight:\s*400;/s');
    expect($stylesheet)->toMatch('/\.editorial-article\s+\.editorial-article__prose\s+:is\(h1, h2, h3, h4, h5, h6\):not\(\.dlf-block \*\)\s+:is\(strong, b\)\s*\{[^}]*font-weight:\s*inherit;/s');
});
it('news and knowledge article headings do not contain bold marks', function () {
    foreach (['insights', 'knowledge'] as $collection) {
        $paths = glob(base_path("content/collections/{$collection}/*.md"));

        expect($paths)->toBeArray();

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
});
it('about page marks only substantial content media', function () {
    $xpath = progressivePageXPath($this, '/over-ons');
    $images = progressiveImages($xpath);

    expect($images->length)->toBeGreaterThanOrEqual(11);

    foreach ($images as $image) {
        assertProgressiveImageContract($image, '/over-ons');
        expect($image->getAttribute('loading'))->toBe('lazy');
    }
});
it('homepage uses eager loading only for its primary photo', function () {
    $xpath = progressivePageXPath($this, '/');
    $primary = $xpath->query('//img[@data-progressive-media and @fetchpriority="high"]');
    $lazy = $xpath->query('//img[@data-progressive-media and @loading="lazy"]');

    expect($primary)->toBeInstanceOf(DOMNodeList::class);
    expect($lazy)->toBeInstanceOf(DOMNodeList::class);
    expect($primary)->toHaveCount(1);
    expect($lazy->length)->toBeGreaterThan(0);
    expect($primary->item(0)?->attributes?->getNamedItem('loading')?->nodeValue)->toBe('eager');
});
it('public page families expose stable progressive media', function () {
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
        $xpath = progressivePageXPath($this, $uri);
        $images = progressiveImages($xpath);

        expect($images->length)->toBeGreaterThan(0, $uri);

        foreach ($images as $image) {
            assertProgressiveImageContract($image, $uri);
        }
    }
});
it('header footer icons and logos are not progressive media', function () {
    $xpath = progressivePageXPath($this, '/over-ons');
    $images = $xpath->query('//header//img | //footer//img');

    expect($images)->toBeInstanceOf(DOMNodeList::class);
    expect($images->length)->toBeGreaterThan(0);

    foreach ($images as $image) {
        expect($image)->toBeInstanceOf(DOMElement::class);
        expect($image->hasAttribute('data-progressive-media'))->toBeFalse();
    }
});
it('desktop footer brand divider spans the full viewport', function () {
    $stylesheet = file_get_contents(resource_path('css/redesign-shell.css'));

    $this->assertNotFalse($stylesheet);
    expect($stylesheet)->toMatch('/@media \(min-width:\s*1024px\)\s*\{.*?\.dlf-footer-brand\s*\{[^}]*margin-inline:\s*calc\(50% - 50vw\);[^}]*padding-inline:\s*calc\(50vw - 50%\);/s');
});
it('mobile footer copyright is centered', function () {
    $stylesheet = file_get_contents(resource_path('css/redesign-shell.css'));

    $this->assertNotFalse($stylesheet);
    expect($stylesheet)->toMatch('/@media \(max-width:\s*639px\)\s*\{.*?\.dlf-footer-bottom\s*>\s*p\s*\{[^}]*text-align:\s*center;/s');
});
it('inline article photography uses the progressive media contract', function () {
    $uris = [
        '/kennis/ai-gedreven-zoekfunctionaliteit-dankzij-vragenai',
        '/kennis/graphql-met-laravel-en-lighthouse',
        '/nieuws/dlf-meetup-bij-dij',
    ];

    foreach ($uris as $uri) {
        $xpath = progressivePageXPath($this, $uri);
        $images = $xpath->query('//article[contains(concat(" ", normalize-space(@class), " "), " editorial-article__prose ")]//img[contains(@src, ".gif") or contains(@src, ".jpg") or contains(@src, ".jpeg") or contains(@src, ".png") or contains(@src, ".webp")]');

        expect($images)->toBeInstanceOf(DOMNodeList::class);
        expect($images->length)->toBeGreaterThan(0, $uri);

        foreach ($images as $image) {
            assertProgressiveImageContract($image, $uri);
        }
    }
});
function assertProgressiveImageContract(DOMElement $image, string $context): void
{
    expect($image->getAttribute('data-media-state'))->toBe('loading', $context);
    expect(['eager', 'lazy'])->toContain($image->getAttribute('loading'));
    expect($image->getAttribute('decoding'))->toBe('async', $context);
    expect($image->hasAttribute('onload'))->toBeFalse($context);
    expect($image->getAttribute('width'))->toMatch('/^[1-9][0-9]*$/', $context);
    expect($image->getAttribute('height'))->toMatch('/^[1-9][0-9]*$/', $context);
    expect($image->parentNode)->toBeInstanceOf(DOMElement::class, $context);

    $frame = $image->parentNode;

    while ($frame instanceof DOMElement && ! $frame->hasAttribute('data-progressive-media-frame')) {
        $frame = $frame->parentNode;
    }

    expect($frame)->toBeInstanceOf(DOMElement::class, "{$context}\n{$image->getAttribute('src')}");
}
/** @return DOMNodeList<DOMElement> */
function progressiveImages(DOMXPath $xpath): DOMNodeList
{
    $images = $xpath->query('//img[@data-progressive-media]');

    expect($images)->toBeInstanceOf(DOMNodeList::class);

    return $images;
}
function progressivePageXPath(TestCase $testCase, string $uri): DOMXPath
{
    $response = $testCase->get($uri);
    expect($response->getStatusCode())->toBe(200, "{$uri}\n{$response->getContent()}");

    $document = new DOMDocument;
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML($response->getContent());
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    return new DOMXPath($document);
}
/** @return list<string> */
function antlersTemplates(): array
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
