<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class ThirdPartyEmbedConsentTest extends TestCase
{
    public function test_external_video_components_defer_iframe_sources_until_consent(): void
    {
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
    }

    public function test_podcast_component_keeps_the_spotify_link_visible_alongside_the_consent_gated_embed(): void
    {
        $component = file_get_contents(
            resource_path('js/components/editorial-react/PodcastControls.tsx'),
        );

        $this->assertNotFalse($component);
        $this->assertStringContainsString('spotifyEmbedUrl(spotifyUrl)', $component);
        $this->assertStringContainsString('data-consent-src=', $component);
        $this->assertStringContainsString('editorial-podcast__spotify-embed', $component);
        $this->assertStringContainsString('href={spotifyUrl}', $component);
    }
}
