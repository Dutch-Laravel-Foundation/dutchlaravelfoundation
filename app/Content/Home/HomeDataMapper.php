<?php

declare(strict_types=1);

namespace App\Content\Home;

use App\Data\Home\AssetData;
use App\Data\Home\ClientData;
use App\Data\Home\ContentCardData;
use App\Data\Home\HomeData;
use App\Data\Home\PartnerData;

final class HomeDataMapper
{
    /** @param array<string, mixed> $response */
    public function map(array $response): HomeData
    {
        return new HomeData(
            latestInsight: $this->mapCard($this->firstEntry($response, 'latestInsight')),
            latestKnowledge: $this->mapCard($this->firstEntry($response, 'latestKnowledge')),
            highlightedInsight: $this->mapCard($this->firstEntry($response, 'highlightedInsight')),
            partners: $this->mapPartners($this->entries($response, 'partners')),
            clients: $this->mapClients($this->entries($response, 'clients')),
        );
    }

    /** @param array<string, mixed>|null $entry */
    private function mapCard(?array $entry): ?ContentCardData
    {
        if ($entry === null) {
            return null;
        }

        return new ContentCardData(
            id: (string) ($entry['id'] ?? ''),
            title: (string) ($entry['title'] ?? ''),
            slug: (string) ($entry['slug'] ?? ''),
            url: $this->nullableString($entry['url'] ?? null),
            category: $this->mapLabel($entry['category'] ?? null),
            introduction: $this->nullableString($entry['introduction'] ?? null),
            featuredImage: $this->mapAsset($entry['featured_image'] ?? null),
        );
    }

    /**
     * @param  array<int, mixed>  $entries
     * @return array<int, PartnerData>
     */
    private function mapPartners(array $entries): array
    {
        $partners = [];

        foreach ($entries as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $logo = $entry['logo'] ?? null;

            $partners[] = new PartnerData(
                id: (string) ($entry['id'] ?? ''),
                title: (string) ($entry['title'] ?? ''),
                slug: (string) ($entry['slug'] ?? ''),
                visible: (bool) ($entry['visible'] ?? false),
                logo: $this->mapAsset(is_array($logo) ? ($logo[0] ?? null) : null),
            );
        }

        return $partners;
    }

    /**
     * @param  array<int, mixed>  $entries
     * @return array<int, ClientData>
     */
    private function mapClients(array $entries): array
    {
        $clientsBySlug = [];

        foreach ($entries as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $slug = (string) ($entry['slug'] ?? '');

            $clientsBySlug[$slug] = new ClientData(
                id: (string) ($entry['id'] ?? ''),
                title: (string) ($entry['title'] ?? ''),
                slug: $slug,
                logo: $this->mapAsset($entry['logo'] ?? null),
            );
        }

        $clients = [];

        foreach (HomeRepository::CURATED_CLIENT_SLUGS as $slug) {
            if (! isset($clientsBySlug[$slug])) {
                continue;
            }

            $clients[] = $clientsBySlug[$slug];
        }

        return $clients;
    }

    private function mapAsset(mixed $asset): ?AssetData
    {
        if (! is_array($asset)) {
            return null;
        }

        return new AssetData(
            id: (string) ($asset['id'] ?? ''),
            url: (string) ($asset['url'] ?? ''),
            permalink: $this->nullableString($asset['permalink'] ?? null),
            path: (string) ($asset['path'] ?? ''),
            extension: (string) ($asset['extension'] ?? ''),
            width: $this->nullableInteger($asset['width'] ?? null),
            height: $this->nullableInteger($asset['height'] ?? null),
            focusCss: $this->nullableString($asset['focus_css'] ?? null),
            alt: $this->nullableString($asset['alt'] ?? null),
        );
    }

    /**
     * @param  array<string, mixed>  $response
     * @return array<string, mixed>|null
     */
    private function firstEntry(array $response, string $key): ?array
    {
        $entry = $this->entries($response, $key)[0] ?? null;

        return is_array($entry) ? $entry : null;
    }

    /**
     * @param  array<string, mixed>  $response
     * @return array<int, mixed>
     */
    private function entries(array $response, string $key): array
    {
        $connection = $response[$key] ?? null;

        if (! is_array($connection)) {
            return [];
        }

        $entries = $connection['data'] ?? null;

        return is_array($entries) ? array_values($entries) : [];
    }

    private function nullableString(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }

    private function mapLabel(mixed $value): ?string
    {
        if (is_array($value)) {
            return $this->nullableString($value['label'] ?? $value['value'] ?? null);
        }

        return $this->nullableString($value);
    }

    private function nullableInteger(mixed $value): ?int
    {
        return is_int($value) ? $value : null;
    }
}
