<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class ThirdPartyEmbedConsentTest extends TestCase
{
    public function testExternalVideoTemplatesDeferIframeSourcesUntilConsent(): void
    {
        $templates = [
            resource_path('views/partials/sets/_video.antlers.html'),
            resource_path('views/templates/members/show.antlers.html'),
            resource_path('views/templates/podcasts/show.antlers.html'),
        ];

        foreach ($templates as $templatePath) {
            $template = file_get_contents($templatePath);

            $this->assertNotFalse($template);
            $this->assertStringContainsString('data-consent-src=', $template, $templatePath);
            $this->assertStringContainsString('partial:embed_consent', $template, $templatePath);
        }
    }

    public function testPodcastTemplateKeepsTheSpotifyLinkVisibleAlongsideTheConsentGatedEmbed(): void
    {
        $template = file_get_contents(
            resource_path('views/templates/podcasts/show.antlers.html'),
        );

        $this->assertNotFalse($template);
        $this->assertStringContainsString('open.spotify.com/embed/', $template);
        $this->assertStringNotContainsString('data-consent-fallback', $template);
        $this->assertStringContainsString('editorial-podcast__spotify-embed', $template);
    }
}
