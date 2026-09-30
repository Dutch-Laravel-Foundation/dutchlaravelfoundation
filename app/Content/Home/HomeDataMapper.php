<?php

declare(strict_types=1);

namespace App\Content\Home;

use App\Data\Home\AssetData;
use App\Data\Home\ClientData;
use App\Data\Home\ContentCardData;
use App\Data\Home\HomeData;
use App\Data\Home\PartnerData;
use Statamic\Facades\Asset;
use Statamic\Facades\Image;

final class HomeDataMapper
{
    /**
     * Glide crops per home card, matching the Antlers templates: [width, height], the first is the fallback src.
     *
     * @var array<string, list<array{int, int}>>
     */
    private const CARD_CROPS = [
        'latestInsight' => [[800, 434], [480, 261], [720, 391], [1000, 543], [1400, 760]],
        'latestKnowledge' => [[800, 480], [480, 288], [720, 432], [1000, 600]],
        'highlightedInsight' => [[800, 514], [480, 309], [720, 463], [1000, 643], [1400, 900]],
    ];

    /** @param array<string, mixed> $response */
    public function map(array $response): HomeData
    {
        return new HomeData(
            latestInsight: $this->mapCard($this->firstEntry($response, 'latestInsight'), self::CARD_CROPS['latestInsight']),
            latestKnowledge: $this->mapCard($this->firstEntry($response, 'latestKnowledge'), self::CARD_CROPS['latestKnowledge']),
            highlightedInsight: $this->mapCard($this->firstEntry($response, 'highlightedInsight'), self::CARD_CROPS['highlightedInsight']),
            partners: $this->mapPartners($this->entries($response, 'partners')),
            clients: $this->mapClients($this->entries($response, 'clients')),
        );
    }

    /**
     * @param  array<string, mixed>|null  $entry
     * @param  list<array{int, int}>  $crops
     */
    private function mapCard(?array $entry, array $crops): ?ContentCardData
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
            featuredImage: $this->mapAsset($entry['featured_image'] ?? null, $crops),
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

    /** @param  list<array{int, int}>  $crops */
    private function mapAsset(mixed $asset, array $crops = []): ?AssetData
    {
        if (! is_array($asset)) {
            return null;
        }

        $variants = $this->cropVariants((string) ($asset['id'] ?? ''), $crops);

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
            src: $variants[0] ?? null,
            srcset: $variants[1] ?? null,
        );
    }

    /**
     * Crop the upload like the Antlers glide tag did. Uploads can have rounded corners baked in; the crop removes them.
     *
     * @param  list<array{int, int}>  $crops
     * @return array{0: string, 1: string}|array{}
     */
    private function cropVariants(string $assetId, array $crops): array
    {
        $asset = $crops === [] || $assetId === '' ? null : Asset::find($assetId);

        if ($asset === null) {
            return [];
        }

        $urls = [];

        foreach ($crops as [$width, $height]) {
            $urls[$width] = Image::manipulate($asset)
                ->fit('crop_focal')
                ->width($width)
                ->height($height)
                ->format('webp')
                ->quality(82)
                ->build();
        }

        $src = $urls[$crops[0][0]];
        ksort($urls);

        return [
            $src,
            implode(', ', array_map(
                static fn (int $width, string $url): string => "{$url} {$width}w",
                array_keys($urls),
                $urls,
            )),
        ];
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
