<?php

declare(strict_types=1);
test('external video components defer iframe sources until consent', function () {
    $components = [
        resource_path('js/components/editorial-react/ContentBlocks.tsx'),
        resource_path('js/components/editorial-react/PodcastMedia.tsx'),
        resource_path('js/pages/Community/MembersShow.tsx'),
    ];

    foreach ($components as $componentPath) {
        $component = file_get_contents($componentPath);

        $this->assertNotFalse($component);
        $this->assertStringContainsString('data-consent-src=', $component, $componentPath);
        $this->assertStringContainsString('hidden', $component, $componentPath);
    }
});
test('podcast component keeps the spotify link visible alongside the consent gated embed', function () {
    $component = file_get_contents(
        resource_path('js/components/editorial-react/PodcastControls.tsx'),
    );

    $this->assertNotFalse($component);
    $this->assertStringContainsString('spotifyEmbedUrl(spotifyUrl)', $component);
    $this->assertStringContainsString('data-consent-src=', $component);
    $this->assertStringContainsString('editorial-podcast__spotify-embed', $component);
    $this->assertStringContainsString('href={spotifyUrl}', $component);
});
