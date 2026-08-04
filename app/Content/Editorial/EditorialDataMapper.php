<?php

declare(strict_types=1);

namespace App\Content\Editorial;

use App\Data\Editorial\ArticleCardData;
use App\Data\Editorial\ArticleIndexData;
use App\Data\Editorial\AssetData;
use App\Data\Editorial\AuthorData;
use App\Data\Editorial\CallToActionData;
use App\Data\Editorial\ContentBlockData;
use App\Data\Editorial\EventCardData;
use App\Data\Editorial\EventData;
use App\Data\Editorial\EventIndexData;
use App\Data\Editorial\InsightData;
use App\Data\Editorial\KnowledgeData;
use App\Data\Editorial\LinkData;
use App\Data\Editorial\PaginationData;
use App\Data\Editorial\PodcastCardData;
use App\Data\Editorial\PodcastData;
use App\Data\Editorial\PodcastIndexData;
use App\Data\SeoData;

final class EditorialDataMapper
{
    /** @param array<string, mixed> $response */
    public function mapArticleIndex(array $response): ArticleIndexData
    {
        $connection = $this->connection($response, 'entries');

        return new ArticleIndexData(
            items: $this->mapItems($connection, fn (array $entry): ArticleCardData => $this->mapArticleCard($entry)),
            pagination: $this->mapPagination($connection),
        );
    }

    /** @param array<string, mixed>|null $entry */
    public function mapInsight(?array $entry): ?InsightData
    {
        if ($entry === null) {
            return null;
        }

        return new InsightData(
            id: (string) ($entry['id'] ?? ''),
            title: (string) ($entry['title'] ?? ''),
            slug: (string) ($entry['slug'] ?? ''),
            url: $this->nullableString($entry['url'] ?? null),
            uri: $this->nullableString($entry['uri'] ?? null),
            category: $this->mapLabel($entry['category'] ?? null),
            date: $this->nullableString($entry['date'] ?? null),
            introduction: $this->nullableString($entry['introduction'] ?? null),
            featuredImage: $this->mapAsset($entry['featured_image'] ?? null),
            content: $this->mapContent($entry['content'] ?? null),
            author: $this->mapInlineAuthor($entry),
            callToAction: $this->mapCallToAction($entry['call_to_action'] ?? null),
            seo: $this->mapSeo($entry),
        );
    }

    /** @param array<string, mixed>|null $entry */
    public function mapKnowledge(?array $entry): ?KnowledgeData
    {
        if ($entry === null) {
            return null;
        }

        return new KnowledgeData(
            id: (string) ($entry['id'] ?? ''),
            title: (string) ($entry['title'] ?? ''),
            slug: (string) ($entry['slug'] ?? ''),
            url: $this->nullableString($entry['url'] ?? null),
            uri: $this->nullableString($entry['uri'] ?? null),
            category: $this->mapLabel($entry['category'] ?? null),
            date: $this->nullableString($entry['date'] ?? null),
            introduction: $this->nullableString($entry['introduction'] ?? null),
            featuredImage: $this->mapAsset($entry['featured_image'] ?? null),
            contentHtml: $this->nullableString($entry['content'] ?? null),
            authors: $this->mapAuthors($entry['authors'] ?? null),
            callToAction: $this->mapCallToAction($entry['call_to_action'] ?? null),
            seo: $this->mapSeo($entry),
        );
    }

    /** @param array<string, mixed> $response */
    public function mapPodcastIndex(array $response): PodcastIndexData
    {
        $connection = $this->connection($response, 'entries');

        return new PodcastIndexData(
            items: $this->mapItems($connection, fn (array $entry): PodcastCardData => $this->mapPodcastCard($entry)),
            pagination: $this->mapPagination($connection),
        );
    }

    /** @param array<string, mixed>|null $entry */
    public function mapPodcast(?array $entry): ?PodcastData
    {
        if ($entry === null) {
            return null;
        }

        return new PodcastData(
            id: (string) ($entry['id'] ?? ''),
            title: (string) ($entry['title'] ?? ''),
            slug: (string) ($entry['slug'] ?? ''),
            url: $this->nullableString($entry['url'] ?? null),
            uri: $this->nullableString($entry['uri'] ?? null),
            summary: (string) ($entry['summary'] ?? ''),
            descriptionHtml: (string) ($entry['description'] ?? ''),
            videoUrl: (string) ($entry['video_url'] ?? ''),
            spotifyUrl: (string) ($entry['spotify_url'] ?? ''),
            thumbnailUrl: (string) ($entry['thumbnail_url'] ?? ''),
            transcriptHtml: (string) ($entry['transcript'] ?? ''),
            publishedAt: (string) ($entry['published_at'] ?? ''),
            callToAction: $this->mapCallToAction($entry['call_to_action'] ?? null),
            seo: $this->mapSeo($entry),
        );
    }

    /** @param array<string, mixed> $response */
    public function mapEventIndex(array $response): EventIndexData
    {
        $past = $this->connection($response, 'past');

        return new EventIndexData(
            upcoming: $this->mapItems(
                $this->connection($response, 'upcoming'),
                fn (array $entry): EventCardData => $this->mapEventCard($entry),
            ),
            past: $this->mapItems(
                $past,
                fn (array $entry): EventCardData => $this->mapEventCard($entry),
            ),
            pagination: $this->mapPagination($past),
        );
    }

    /** @param array<string, mixed>|null $entry */
    public function mapEvent(?array $entry): ?EventData
    {
        if ($entry === null) {
            return null;
        }

        return new EventData(
            id: (string) ($entry['id'] ?? ''),
            title: (string) ($entry['title'] ?? ''),
            slug: (string) ($entry['slug'] ?? ''),
            url: $this->nullableString($entry['url'] ?? null),
            uri: $this->nullableString($entry['uri'] ?? null),
            type: $this->mapLabel($entry['type'] ?? null),
            introduction: $this->nullableString($entry['introduction'] ?? null),
            featuredImage: $this->mapAsset($entry['featured_image'] ?? null),
            dateStart: $this->nullableString($entry['date_start'] ?? null),
            timeStart: $this->nullableString($entry['time_start'] ?? null),
            timeEnd: $this->nullableString($entry['time_end'] ?? null),
            location: $this->nullableString($entry['location'] ?? null),
            address: $this->nullableString($entry['address'] ?? null),
            signupLink: $this->nullableString($entry['signup_link'] ?? null),
            content: $this->mapContent($entry['content'] ?? null),
            seo: $this->mapSeo($entry),
        );
    }

    /** @param array<string, mixed> $entry */
    private function mapArticleCard(array $entry): ArticleCardData
    {
        return new ArticleCardData(
            id: (string) ($entry['id'] ?? ''),
            title: (string) ($entry['title'] ?? ''),
            slug: (string) ($entry['slug'] ?? ''),
            url: $this->nullableString($entry['url'] ?? null),
            uri: $this->nullableString($entry['uri'] ?? null),
            category: $this->mapLabel($entry['category'] ?? null),
            date: $this->nullableString($entry['date'] ?? null),
            introduction: $this->nullableString($entry['introduction'] ?? null),
            featuredImage: $this->mapAsset($entry['featured_image'] ?? null),
        );
    }

    /** @param array<string, mixed> $entry */
    private function mapPodcastCard(array $entry): PodcastCardData
    {
        return new PodcastCardData(
            id: (string) ($entry['id'] ?? ''),
            title: (string) ($entry['title'] ?? ''),
            slug: (string) ($entry['slug'] ?? ''),
            url: $this->nullableString($entry['url'] ?? null),
            uri: $this->nullableString($entry['uri'] ?? null),
            summary: (string) ($entry['summary'] ?? ''),
            thumbnailUrl: (string) ($entry['thumbnail_url'] ?? ''),
            publishedAt: (string) ($entry['published_at'] ?? ''),
        );
    }

    /** @param array<string, mixed> $entry */
    private function mapEventCard(array $entry): EventCardData
    {
        return new EventCardData(
            id: (string) ($entry['id'] ?? ''),
            title: (string) ($entry['title'] ?? ''),
            slug: (string) ($entry['slug'] ?? ''),
            url: $this->nullableString($entry['url'] ?? null),
            uri: $this->nullableString($entry['uri'] ?? null),
            type: $this->mapLabel($entry['type'] ?? null),
            dateStart: $this->nullableString($entry['date_start'] ?? null),
            introduction: $this->nullableString($entry['introduction'] ?? null),
            featuredImage: $this->mapAsset($entry['featured_image'] ?? null),
        );
    }

    /**
     * @param  array<string, mixed>  $connection
     *
     * @template T
     *
     * @param  callable(array<string, mixed>): T  $mapper
     * @return array<int, T>
     */
    private function mapItems(array $connection, callable $mapper): array
    {
        $items = [];
        $entries = $connection['data'] ?? null;

        if (! is_array($entries)) {
            return $items;
        }

        foreach ($entries as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $items[] = $mapper($entry);
        }

        return $items;
    }

    /** @param array<string, mixed> $connection */
    private function mapPagination(array $connection): PaginationData
    {
        return new PaginationData(
            total: (int) ($connection['total'] ?? 0),
            perPage: (int) ($connection['per_page'] ?? 10),
            currentPage: (int) ($connection['current_page'] ?? 1),
            from: $this->nullableInteger($connection['from'] ?? null),
            to: $this->nullableInteger($connection['to'] ?? null),
            lastPage: (int) ($connection['last_page'] ?? 1),
            hasMorePages: (bool) ($connection['has_more_pages'] ?? false),
        );
    }

    /** @return array<int, ContentBlockData> */
    private function mapContent(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $blocks = [];

        foreach ($value as $block) {
            if (! is_array($block)) {
                continue;
            }

            $blocks[] = new ContentBlockData(
                kind: (string) ($block['__typename'] ?? ''),
                type: (string) ($block['type'] ?? ''),
                id: $this->nullableString($block['id'] ?? null),
                html: $this->nullableString($block['text'] ?? null),
                value: $this->contentValue($block),
                asset: $this->mapAsset($block['image'] ?? null),
            );
        }

        return $blocks;
    }

    /** @param array<string, mixed> $block */
    private function contentValue(array $block): ?string
    {
        foreach (['video', 'spacer', 'line'] as $field) {
            $value = $this->nullableString($block[$field] ?? null);

            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    /** @param array<string, mixed> $entry */
    private function mapInlineAuthor(array $entry): ?AuthorData
    {
        $name = $this->nullableString($entry['author_name'] ?? null);

        if ($name === null) {
            return null;
        }

        return new AuthorData(
            id: null,
            name: $name,
            role: $this->nullableString($entry['author_role'] ?? null),
            bio: $this->nullableString($entry['author_bio'] ?? null),
            image: $this->mapAsset($entry['author_image'] ?? null),
            imageUrl: null,
            profileUrl: $this->mapLink($entry['author_link'] ?? null)?->url,
            linkedinUrl: null,
            websiteUrl: null,
        );
    }

    /** @return array<int, AuthorData> */
    private function mapAuthors(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $authors = [];

        foreach ($value as $author) {
            if (! is_array($author)) {
                continue;
            }

            $authors[] = new AuthorData(
                id: $this->nullableString($author['id'] ?? null),
                name: (string) ($author['title'] ?? ''),
                role: $this->nullableString($author['job_title'] ?? null),
                bio: $this->nullableString($author['description'] ?? null),
                image: $this->mapAsset($author['photo'] ?? null),
                imageUrl: $this->nullableString($author['photo_url'] ?? null),
                profileUrl: null,
                linkedinUrl: $this->mapLink($author['linkedin_url'] ?? null)?->url,
                websiteUrl: $this->mapLink($author['website_url'] ?? null)?->url,
            );
        }

        return $authors;
    }

    private function mapCallToAction(mixed $value): ?CallToActionData
    {
        if (! is_array($value)) {
            return null;
        }

        $benefits = [];

        foreach (($value['benefits'] ?? []) as $benefit) {
            if (is_string($benefit)) {
                $benefits[] = $benefit;
            }
        }

        return new CallToActionData(
            id: (string) ($value['id'] ?? ''),
            title: (string) ($value['title'] ?? ''),
            description: $this->nullableString($value['description'] ?? null),
            eyebrow: $this->nullableString($value['eyebrow'] ?? null),
            benefits: $benefits,
            link: $this->mapLink($value['link'] ?? null),
            secondaryLink: $this->mapLink($value['link_2'] ?? null),
            theme: $this->mapLabel($value['theme'] ?? null),
            buttonText: $this->nullableString($value['button_text'] ?? null),
            buttonStyle: $this->mapLabel($value['button_style'] ?? null),
            secondaryButtonText: $this->nullableString($value['button_text_2'] ?? null),
            secondaryButtonStyle: $this->mapLabel($value['button_style_2'] ?? null),
        );
    }

    private function mapAsset(mixed $value): ?AssetData
    {
        if (! is_array($value)) {
            return null;
        }

        return new AssetData(
            id: (string) ($value['id'] ?? ''),
            url: $this->nullableString($value['url'] ?? null),
            permalink: $this->nullableString($value['permalink'] ?? null),
            path: (string) ($value['path'] ?? ''),
            extension: (string) ($value['extension'] ?? ''),
            width: $this->nullableInteger($value['width'] ?? null),
            height: $this->nullableInteger($value['height'] ?? null),
            focusCss: $this->nullableString($value['focus_css'] ?? null),
            alt: $this->nullableString($value['alt'] ?? null),
        );
    }

    private function mapLink(mixed $value): ?LinkData
    {
        if (! is_array($value)) {
            return null;
        }

        $url = $this->nullableString($value['url'] ?? null);
        $title = $this->nullableString($value['title'] ?? null);

        if ($url === null && $title === null) {
            return null;
        }

        return new LinkData($url, $title);
    }

    /** @param array<string, mixed> $entry */
    private function mapSeo(array $entry): SeoData
    {
        return new SeoData(
            title: $this->nullableString($entry['meta_title'] ?? null),
            description: $this->nullableString($entry['meta_description'] ?? null),
            keywords: $this->nullableString($entry['meta_keywords'] ?? null),
        );
    }

    private function mapLabel(mixed $value): ?string
    {
        if (! is_array($value)) {
            return $this->nullableString($value);
        }

        return $this->nullableString($value['label'] ?? $value['value'] ?? null);
    }

    /**
     * @param  array<string, mixed>  $response
     * @return array<string, mixed>
     */
    private function connection(array $response, string $key): array
    {
        $connection = $response[$key] ?? null;

        return is_array($connection) ? $connection : [];
    }

    private function nullableString(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }

    private function nullableInteger(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
