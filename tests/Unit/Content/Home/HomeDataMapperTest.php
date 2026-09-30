<?php

declare(strict_types=1);

namespace Tests\Unit\Content\Home;

use App\Content\Home\HomeDataMapper;
use App\Data\Home\AssetData;
use App\Data\Home\ClientData;
use App\Data\Home\ContentCardData;
use App\Data\Home\HomeData;
use App\Data\Home\PartnerData;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class HomeDataMapperTest extends TestCase
{
    #[Test]
    public function it_maps_graphql_content_to_strongly_typed_home_data(): void
    {
        $mapper = new HomeDataMapper;

        $home = $mapper->map([
            'latestInsight' => ['data' => [$this->card('insight', 'Latest insight')]],
            'latestKnowledge' => ['data' => [$this->card('knowledge', 'Latest knowledge')]],
            'highlightedInsight' => ['data' => [$this->card('highlight', 'Highlighted insight')]],
            'partners' => ['data' => [[
                'id' => 'partner-id',
                'title' => 'Laravel Shift',
                'slug' => 'laravel-shift',
                'visible' => true,
                'logo' => [$this->asset('partners/laravel-shift.svg', 'Laravel Shift')],
            ]]],
            'clients' => ['data' => [[
                'id' => 'client-id',
                'title' => 'AVIA',
                'slug' => 'avia',
                'logo' => $this->asset('clients/avia.svg', 'AVIA'),
            ]]],
        ]);

        $this->assertInstanceOf(HomeData::class, $home);
        $this->assertInstanceOf(ContentCardData::class, $home->latestInsight);
        $this->assertInstanceOf(ContentCardData::class, $home->latestKnowledge);
        $this->assertInstanceOf(ContentCardData::class, $home->highlightedInsight);
        $this->assertInstanceOf(AssetData::class, $home->latestInsight->featuredImage);
        $this->assertInstanceOf(PartnerData::class, $home->partners[0]);
        $this->assertInstanceOf(AssetData::class, $home->partners[0]->logo);
        $this->assertInstanceOf(ClientData::class, $home->clients[0]);
        $this->assertInstanceOf(AssetData::class, $home->clients[0]->logo);
        $this->assertSame('Latest insight', $home->latestInsight->title);
        $this->assertSame('Netwerk', $home->latestInsight->category);
        $this->assertSame('focus-position: 50% 50%', $home->latestInsight->featuredImage->focusCss);
        $this->assertTrue($home->partners[0]->visible);
        $this->assertSame('avia', $home->clients[0]->slug);
    }

    #[Test]
    public function it_preserves_the_curated_client_order_from_the_homepage(): void
    {
        $mapper = new HomeDataMapper;

        $home = $mapper->map([
            'latestInsight' => ['data' => []],
            'latestKnowledge' => ['data' => []],
            'highlightedInsight' => ['data' => []],
            'partners' => ['data' => []],
            'clients' => ['data' => [
                $this->client('inventum'),
                $this->client('avia'),
                $this->client('de-verbouwcalculator'),
                $this->client('dropday'),
            ]],
        ]);

        $this->assertNull($home->latestInsight);
        $this->assertNull($home->latestKnowledge);
        $this->assertNull($home->highlightedInsight);
        $this->assertSame(
            ['de-verbouwcalculator', 'dropday', 'avia', 'inventum'],
            array_map(static fn (ClientData $client): string => $client->slug, $home->clients),
        );
    }

    /** @return array<string, mixed> */
    private function card(string $slug, string $title): array
    {
        return [
            'id' => "{$slug}-id",
            'title' => $title,
            'slug' => $slug,
            'url' => "/{$slug}",
            'introduction' => "Introduction for {$title}",
            'category' => ['value' => 'Netwerk', 'label' => 'Netwerk'],
            'featured_image' => $this->asset("images/{$slug}.jpg", $title),
        ];
    }

    /** @return array<string, mixed> */
    private function client(string $slug): array
    {
        return [
            'id' => "{$slug}-id",
            'title' => ucfirst($slug),
            'slug' => $slug,
            'logo' => $this->asset("clients/{$slug}.svg", ucfirst($slug)),
        ];
    }

    /** @return array<string, mixed> */
    private function asset(string $path, string $alt): array
    {
        return [
            'id' => $path,
            'url' => "/assets/{$path}",
            'permalink' => "https://dutchlaravelfoundation.nl/assets/{$path}",
            'path' => $path,
            'extension' => pathinfo($path, PATHINFO_EXTENSION),
            'width' => 1200,
            'height' => 800,
            'focus_css' => 'focus-position: 50% 50%',
            'alt' => $alt,
        ];
    }
}
