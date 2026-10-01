<?php

declare(strict_types=1);
test('progressive media frames use a white striped background', function () {
    $stylesheet = file_get_contents(resource_path('css/progressive-media.css'));

    $this->assertNotFalse($stylesheet);
    $this->assertStringContainsString('background-color: #fff;', $stylesheet);
    $this->assertStringContainsString('repeating-linear-gradient', $stylesheet);
    $this->assertStringContainsString('--progressive-media-opacity-duration: 0ms;', $stylesheet);
});
test('inline article images do not expose their progressive frame', function () {
    $stylesheet = file_get_contents(resource_path('css/redesign-editorial.css'));

    $this->assertNotFalse($stylesheet);
    expect($stylesheet)->toMatch('/\.editorial-article \.editorial-article__prose \.dlf-inline-progressive-media\s*\{[^}]*margin-block:\s*1\.375rem;/s');
    expect($stylesheet)->toMatch('/\.editorial-article \.editorial-article__prose \.dlf-inline-progressive-media > img\s*\{[^}]*margin-block:\s*0;/s');
});
test('article rails keep page spacing separate from prose spacing', function () {
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
test('article toc keeps space below the dynamic header', function () {
    $stylesheet = file_get_contents(resource_path('css/redesign-editorial.css'));

    $this->assertNotFalse($stylesheet);
    expect($stylesheet)->toMatch('/\.editorial-toc\s*\{[^}]*top:\s*calc\(var\(--dlf-header-visible-height,\s*0px\) \+ 1\.5rem\);/s');
});
test('larafest article uses level two section headings for the table of contents', function () {
    $response = $this->withHeaders(inertiaHeaders())
        ->get('/nieuws/larafest-2026-security-platforms-en-escape-boxes-aan-zee');
    $blocks = $response->json('props.editorial.content');

    $response->assertOk()->assertHeader('X-Inertia', 'true');
    expect($blocks)->toBeArray();

    $html = collect($blocks)->pluck('html')->filter()->implode('');
    preg_match_all('/<h2\b[^>]*>(.*?)<\/h2>/s', $html, $headings);

    expect(array_map(static fn (string $heading): string => trim(strip_tags($heading)), $headings[1]))->toBe([
        'Worms, packages en Shai-Hulud',
        'Praktijkverhalen uit echte platformen',
        'Eten, escape boxes en bijpraten',
    ]);
});
test('tablet article hero uses the taller image and article copy width', function () {
    $stylesheet = file_get_contents(resource_path('css/redesign-editorial.css'));

    $this->assertNotFalse($stylesheet);
    expect($stylesheet)->toMatch('/@media \(min-width:\s*640px\) and \(max-width:\s*1023px\)\s*\{.*?\.editorial-article__figure\s*\{[^}]*min-height:\s*22\.5rem;/s');
    expect($stylesheet)->toMatch('/@media \(min-width:\s*640px\) and \(max-width:\s*1023px\)\s*\{.*?\.editorial-article__head > \*\s*\{[^}]*max-width:\s*38rem;[^}]*margin-inline:\s*auto;/s');
    expect($stylesheet)->toMatch('/@media \(min-width:\s*640px\) and \(max-width:\s*1023px\)\s*\{.*?\.editorial-article__head\s*\{[^}]*align-items:\s*center;/s');
});
test('emble article does not contain manual break nodes', function () {
    $article = file_get_contents(base_path('content/collections/insights/2026-04-13-2200.emble-ontwikkelaars-pur-sang-blijven-zich-door-ontwikkelen.md'));

    $this->assertNotFalse($article);
    $this->assertStringNotContainsString('type: hardBreak', $article);
});
test('news and knowledge articles do not contain manual breaks', function () {
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
test('article prose headings use normal weight including bold content', function () {
    $stylesheet = file_get_contents(resource_path('css/redesign-editorial.css'));

    $this->assertNotFalse($stylesheet);
    expect($stylesheet)->toMatch('/\.editorial-article \.editorial-article__prose :is\(h1, h2, h3, h4, h5, h6\):not\(\.dlf-block \*\)\s*\{[^}]*font-weight:\s*400;/s');
    expect($stylesheet)->toMatch('/\.editorial-article\s+\.editorial-article__prose\s+:is\(h1, h2, h3, h4, h5, h6\):not\(\.dlf-block \*\)\s+:is\(strong, b\)\s*\{[^}]*font-weight:\s*inherit;/s');
});
test('news and knowledge article headings do not contain bold marks', function () {
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
test('about page marks only substantial content media', function () {
    $response = $this->withHeaders(inertiaHeaders())->get('/over-ons');
    $component = file_get_contents(resource_path('js/pages/PublicPages/About.tsx'));

    $response->assertOk()->assertJsonPath('component', 'PublicPages/About');
    $this->assertNotFalse($component);
    expect(substr_count($component, '<ProgressiveImage'))->toBe(3);
    expect(substr_count($component, 'data-progressive-media-frame'))->toBe(3);
    expect(substr_count($component, 'decoding="async"'))->toBe(3);
});
test('homepage uses eager loading only for its primary photo', function () {
    $hero = file_get_contents(resource_path('js/components/home/HomeHero.tsx'));
    $community = file_get_contents(resource_path('js/components/home/CurrentCommunity.tsx'));

    $this->assertNotFalse($hero);
    $this->assertNotFalse($community);
    expect(substr_count($hero, 'fetchPriority="high"'))->toBe(1);
    expect(substr_count($hero, 'loading="eager"'))->toBe(1);
    $this->assertStringContainsString('loading="lazy"', $community);
});
test('public page families expose stable progressive media', function () {
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
});
test('header footer icons and logos are not progressive media', function () {
    $header = file_get_contents(resource_path('js/components/site/Header.tsx'));
    $footer = file_get_contents(resource_path('js/components/site/Footer.tsx'));

    $this->assertNotFalse($header);
    $this->assertNotFalse($footer);
    $this->assertStringContainsString('<img', $header);
    $this->assertStringContainsString('<img', $footer);
    $this->assertStringNotContainsString('ProgressiveImage', $header);
    $this->assertStringNotContainsString('ProgressiveImage', $footer);
});
test('desktop footer brand divider spans the full viewport', function () {
    $stylesheet = file_get_contents(resource_path('css/redesign-shell.css'));

    $this->assertNotFalse($stylesheet);
    expect($stylesheet)->toMatch('/@media \(min-width:\s*1024px\)\s*\{.*?\.dlf-footer-brand\s*\{[^}]*margin-inline:\s*calc\(50% - 50vw\);[^}]*padding-inline:\s*calc\(50vw - 50%\);/s');
});
test('mobile footer copyright is centered', function () {
    $stylesheet = file_get_contents(resource_path('css/redesign-shell.css'));

    $this->assertNotFalse($stylesheet);
    expect($stylesheet)->toMatch('/@media \(max-width:\s*639px\)\s*\{.*?\.dlf-footer-bottom\s*>\s*p\s*\{[^}]*text-align:\s*center;/s');
});
test('inline article photography is preserved in the inertia dto', function () {
    $uris = [
        '/kennis/ai-gedreven-zoekfunctionaliteit-dankzij-vragenai',
        '/kennis/graphql-met-laravel-en-lighthouse',
        '/nieuws/dlf-meetup-bij-dij',
    ];

    foreach ($uris as $uri) {
        $response = $this->withHeaders(inertiaHeaders())->get($uri);
        $editorial = $response->json('props.editorial');

        $response->assertOk()->assertHeader('X-Inertia', 'true');
        expect($editorial)->toBeArray();
        $this->assertStringContainsString('<img', json_encode($editorial, JSON_THROW_ON_ERROR), $uri);
    }
});
/** @return array<string, string> */
