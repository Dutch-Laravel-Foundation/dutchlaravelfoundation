<?php

declare(strict_types=1);

namespace App\Services\Seo;

use Illuminate\Support\Str;
use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Entry as EntryRepository;
use Statamic\Facades\GlobalSet;

final class SeoMetadata
{
    public function title(?Entry $entry): string
    {
        $siteName = $this->organizationValue('title', 'Dutch Laravel Foundation');
        $explicitTitle = $this->stringValue($entry?->get('meta_title'));
        $entryTitle = $explicitTitle ?? $this->stringValue($entry?->get('title'));

        if (
            $explicitTitle === null
            && $entryTitle !== null
            && $entry?->collection()?->handle() === 'internships'
        ) {
            $entryTitle = "Laravel-stage bij {$entryTitle}";
        }

        if ($entryTitle === null || $entryTitle === 'Home') {
            return $siteName;
        }

        if (Str::contains($entryTitle, $siteName, ignoreCase: true)) {
            return $entryTitle;
        }

        return "{$entryTitle} | {$siteName}";
    }

    public function description(?Entry $entry): string
    {
        $explicitDescription = $this->stringValue($entry?->get('meta_description'));

        if ($explicitDescription !== null) {
            return $this->normalizeDescription($explicitDescription);
        }

        $fallbackFields = match ($entry?->collection()?->handle()) {
            'podcasts' => ['summary', 'description'],
            'cases', 'insights', 'knowledge' => ['introduction'],
            'events' => ['introduction', 'description'],
            'internships', 'members' => ['description'],
            default => ['introduction', 'summary', 'description'],
        };

        foreach ($fallbackFields as $field) {
            $description = $this->stringValue($entry?->get($field));

            if ($description !== null) {
                return $this->normalizeDescription($description);
            }
        }

        return $this->globalSeoValue(
            'meta_description',
            'De kennis- en brancheorganisatie voor Laravel developers',
        );
    }

    public function canonicalUrl(?Entry $entry): string
    {
        $baseUrl = rtrim((string) config('app.url'), '/');
        $uri = $entry?->uri() ?? request()->getPathInfo();

        if ($uri === '' || $uri === '/') {
            return "{$baseUrl}/";
        }

        return $baseUrl . '/' . ltrim($uri, '/');
    }

    public function openGraphType(?Entry $entry): string
    {
        return in_array(
            $entry?->collection()?->handle(),
            ['cases', 'insights', 'knowledge', 'podcasts'],
            true,
        ) ? 'article' : 'website';
    }

    public function jsonLd(?Entry $entry): string
    {
        $graph = [$this->organizationSchema()];

        if ($entry !== null && ($pageSchema = $this->pageSchema($entry)) !== null) {
            $graph[] = $pageSchema;
        }

        return json_encode([
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function currentEntry(): ?Entry
    {
        return EntryRepository::findByUri(request()->getPathInfo());
    }

    /**
     * @return array<string, mixed>
     */
    private function organizationSchema(): array
    {
        $rootUrl = rtrim((string) config('app.url'), '/') . '/';

        return [
            '@type' => 'Organization',
            '@id' => "{$rootUrl}#organization",
            'name' => $this->organizationValue('title', 'Dutch Laravel Foundation'),
            'url' => $rootUrl,
            'logo' => [
                '@type' => 'ImageObject',
                'url' => $rootUrl . 'apple-touch-icon.png',
            ],
            'email' => $this->organizationValue('email', 'info@dutchlaravelfoundation.nl'),
            'telephone' => $this->organizationValue('phone', '+31 (0)88 73 33 319'),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $this->organizationValue('address', 'Edelgasstraat 103'),
                'postalCode' => $this->organizationValue('zipcode', '2718 TE'),
                'addressLocality' => $this->organizationValue('city', 'Zoetermeer'),
                'addressCountry' => 'NL',
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function pageSchema(Entry $entry): ?array
    {
        $type = match ($entry->collection()?->handle()) {
            'knowledge' => 'Article',
            'insights' => 'NewsArticle',
            'podcasts' => 'PodcastEpisode',
            'cases' => 'CreativeWork',
            default => null,
        };

        if ($type === null) {
            return null;
        }

        $canonicalUrl = $this->canonicalUrl($entry);
        $schema = [
            '@type' => $type,
            '@id' => "{$canonicalUrl}#content",
            'url' => $canonicalUrl,
            'mainEntityOfPage' => $canonicalUrl,
            'name' => $this->stringValue($entry->get('title')),
            'description' => $this->description($entry),
            'publisher' => [
                '@id' => rtrim((string) config('app.url'), '/') . '/#organization',
            ],
        ];

        if (in_array($type, ['Article', 'NewsArticle'], true)) {
            $schema['headline'] = $this->stringValue($entry->get('title'));
            $schema['author'] = $this->authors($entry);
        }

        if ($publishedAt = $this->publishedAt($entry)) {
            $schema['datePublished'] = $publishedAt;
        }

        if ($updatedAt = $this->updatedAt($entry)) {
            $schema['dateModified'] = $updatedAt;
        }

        if ($imageUrl = $this->imageUrl($entry)) {
            $schema['image'] = $imageUrl;
        }

        return array_filter(
            $schema,
            static fn (mixed $value): bool => $value !== null && $value !== [],
        );
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function authors(Entry $entry): array
    {
        $authors = collect($entry->get('authors'))
            ->filter()
            ->map(function (mixed $authorId): ?array {
                $author = is_string($authorId) ? EntryRepository::find($authorId) : null;
                $name = $this->stringValue($author?->get('title'));

                if ($name === null) {
                    return null;
                }

                $person = [
                    '@type' => 'Person',
                    'name' => $name,
                ];

                if ($url = $this->stringValue($author?->get('website_url'))) {
                    $person['url'] = $url;
                }

                return $person;
            })
            ->filter()
            ->values()
            ->all();

        if ($authors !== []) {
            return $authors;
        }

        if ($authorName = $this->stringValue($entry->get('author_name'))) {
            return [[
                '@type' => 'Person',
                'name' => $authorName,
            ]];
        }

        return [[
            '@type' => 'Organization',
            '@id' => rtrim((string) config('app.url'), '/') . '/#organization',
            'name' => $this->organizationValue('title', 'Dutch Laravel Foundation'),
        ]];
    }

    private function publishedAt(Entry $entry): ?string
    {
        return $entry->date()?->toAtomString();
    }

    private function updatedAt(Entry $entry): ?string
    {
        $updatedAt = $entry->get('updated_at');

        if (! is_numeric($updatedAt)) {
            return null;
        }

        return now()->setTimestamp((int) $updatedAt)->toAtomString();
    }

    private function imageUrl(Entry $entry): ?string
    {
        if ($entry->collection()?->handle() === 'podcasts') {
            return $this->stringValue($entry->get('thumbnail_url'));
        }

        $image = $entry->augmentedValue('featured_image')->value();

        if ($image instanceof Asset) {
            return $this->absoluteUrl($image->url());
        }

        return null;
    }

    private function organizationValue(string $key, string $fallback): string
    {
        $variables = GlobalSet::findByHandle('dlf')?->inCurrentSite();

        return $this->stringValue($variables?->get($key)) ?? $fallback;
    }

    private function globalSeoValue(string $key, string $fallback): string
    {
        $variables = GlobalSet::findByHandle('seo')?->inCurrentSite();

        return $this->stringValue($variables?->get($key)) ?? $fallback;
    }

    private function normalizeDescription(string $description): string
    {
        $html = Str::markdown($description);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\\s+/u', ' ', $text) ?? $text;

        return Str::limit(trim($text), 160, '…');
    }

    private function absoluteUrl(string $url): string
    {
        if (Str::contains($url, '://')) {
            return $url;
        }

        return rtrim((string) config('app.url'), '/') . '/' . ltrim($url, '/');
    }

    private function stringValue(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
