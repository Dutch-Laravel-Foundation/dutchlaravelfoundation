<?php

declare(strict_types=1);
use App\Content\Home\HomeDataMapper;
use App\Data\Home\AssetData;
use App\Data\Home\ClientData;
use App\Data\Home\ContentCardData;
use App\Data\Home\HomeData;
use App\Data\Home\PartnerData;

it('maps graphql content to strongly typed home data', function () {
    $mapper = new HomeDataMapper;

    $home = $mapper->map([
        'latestInsight' => ['data' => [card('insight', 'Latest insight')]],
        'latestKnowledge' => ['data' => [card('knowledge', 'Latest knowledge')]],
        'highlightedInsight' => ['data' => [card('highlight', 'Highlighted insight')]],
        'partners' => ['data' => [[
            'id' => 'partner-id',
            'title' => 'Laravel Shift',
            'slug' => 'laravel-shift',
            'visible' => true,
            'logo' => [homeAsset('partners/laravel-shift.svg', 'Laravel Shift')],
        ]]],
        'clients' => ['data' => [[
            'id' => 'client-id',
            'title' => 'AVIA',
            'slug' => 'avia',
            'logo' => homeAsset('clients/avia.svg', 'AVIA'),
        ]]],
    ]);

    expect($home)->toBeInstanceOf(HomeData::class);
    expect($home->latestInsight)->toBeInstanceOf(ContentCardData::class);
    expect($home->latestKnowledge)->toBeInstanceOf(ContentCardData::class);
    expect($home->highlightedInsight)->toBeInstanceOf(ContentCardData::class);
    expect($home->latestInsight->featuredImage)->toBeInstanceOf(AssetData::class);
    expect($home->partners[0])->toBeInstanceOf(PartnerData::class);
    expect($home->partners[0]->logo)->toBeInstanceOf(AssetData::class);
    expect($home->clients[0])->toBeInstanceOf(ClientData::class);
    expect($home->clients[0]->logo)->toBeInstanceOf(AssetData::class);
    expect($home->latestInsight->title)->toBe('Latest insight');
    expect($home->latestInsight->category)->toBe('Netwerk');
    expect($home->latestInsight->featuredImage->focusCss)->toBe('focus-position: 50% 50%');
    expect($home->partners[0]->visible)->toBeTrue();
    expect($home->clients[0]->slug)->toBe('avia');
});
it('preserves the curated client order from the homepage', function () {
    $mapper = new HomeDataMapper;

    $home = $mapper->map([
        'latestInsight' => ['data' => []],
        'latestKnowledge' => ['data' => []],
        'highlightedInsight' => ['data' => []],
        'partners' => ['data' => []],
        'clients' => ['data' => [
            client('inventum'),
            client('avia'),
            client('de-verbouwcalculator'),
            client('dropday'),
        ]],
    ]);

    expect($home->latestInsight)->toBeNull();
    expect($home->latestKnowledge)->toBeNull();
    expect($home->highlightedInsight)->toBeNull();
    expect(array_map(static fn (ClientData $client): string => $client->slug, $home->clients))->toBe(['de-verbouwcalculator', 'dropday', 'avia', 'inventum']);
});
/** @return array<string, mixed> */
function card(string $slug, string $title): array
{
    return [
        'id' => "{$slug}-id",
        'title' => $title,
        'slug' => $slug,
        'url' => "/{$slug}",
        'introduction' => "Introduction for {$title}",
        'category' => ['value' => 'Netwerk', 'label' => 'Netwerk'],
        'featured_image' => homeAsset("images/{$slug}.jpg", $title),
    ];
}
/** @return array<string, mixed> */
function client(string $slug): array
{
    return [
        'id' => "{$slug}-id",
        'title' => ucfirst($slug),
        'slug' => $slug,
        'logo' => homeAsset("clients/{$slug}.svg", ucfirst($slug)),
    ];
}
/** @return array<string, mixed> */
function homeAsset(string $path, string $alt): array
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
