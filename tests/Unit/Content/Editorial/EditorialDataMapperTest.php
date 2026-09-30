<?php

declare(strict_types=1);

namespace Tests\Unit\Content\Editorial;

use App\Content\Editorial\EditorialDataMapper;
use App\Data\Editorial\ArticleCardData;
use App\Data\Editorial\AuthorData;
use App\Data\Editorial\ContentBlockData;
use App\Data\Editorial\EventData;
use App\Data\Editorial\EventIndexData;
use App\Data\Editorial\InsightData;
use App\Data\Editorial\KnowledgeData;
use App\Data\Editorial\PodcastData;
use App\Data\Editorial\PodcastIndexData;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EditorialDataMapperTest extends TestCase
{
    #[Test]
    public function it_maps_paginated_article_cards_and_pagination_metadata(): void
    {
        $index = (new EditorialDataMapper)->mapArticleIndex([
            'entries' => [
                'data' => [$this->articleCard()],
                'total' => 21,
                'per_page' => 10,
                'current_page' => 2,
                'last_page' => 3,
                'has_more_pages' => true,
            ],
        ]);

        $this->assertInstanceOf(ArticleCardData::class, $index->items[0]);
        $this->assertSame('Netwerk', $index->items[0]->category);
        $this->assertSame('/assets/featured.jpg', $index->items[0]->featuredImage?->url);
        $this->assertSame(2, $index->pagination->currentPage);
        $this->assertSame(3, $index->pagination->lastPage);
        $this->assertTrue($index->pagination->hasMorePages);
    }

    #[Test]
    public function it_maps_an_insight_with_rendered_bard_blocks_author_cta_and_seo(): void
    {
        $insight = (new EditorialDataMapper)->mapInsight($this->insight());

        $this->assertInstanceOf(InsightData::class, $insight);
        $this->assertSame('<p>Introduction</p>', $insight->introduction);
        $this->assertInstanceOf(ContentBlockData::class, $insight->content[0]);
        $this->assertSame('text', $insight->content[0]->type);
        $this->assertSame('<h2>Heading</h2>', $insight->content[0]->html);
        $this->assertSame('https://youtube.test/video', $insight->content[1]->value);
        $this->assertInstanceOf(AuthorData::class, $insight->author);
        $this->assertSame('Ada', $insight->author?->name);
        $this->assertSame('Join us', $insight->callToAction?->title);
        $this->assertSame('SEO title', $insight->seo->title);
    }

    #[Test]
    public function it_maps_a_knowledge_article_with_ordered_related_authors(): void
    {
        $entry = $this->baseDetail();
        $entry['content'] = '<h2>Knowledge</h2>';
        $entry['authors'] = [
            [
                'id' => 'author-1',
                'title' => 'Taylor',
                'job_title' => 'Developer',
                'description' => '<p>Biography</p>',
                'photo' => $this->asset('author.jpg'),
                'photo_url' => null,
                'linkedin_url' => ['url' => 'https://linkedin.test/taylor', 'title' => null],
                'website_url' => ['url' => 'https://example.test', 'title' => 'Website'],
            ],
        ];

        $knowledge = (new EditorialDataMapper)->mapKnowledge($entry);

        $this->assertInstanceOf(KnowledgeData::class, $knowledge);
        $this->assertSame('<h2>Knowledge</h2>', $knowledge->contentHtml);
        $this->assertSame('Taylor', $knowledge->authors[0]->name);
        $this->assertSame('https://linkedin.test/taylor', $knowledge->authors[0]->linkedinUrl);
    }

    #[Test]
    public function it_maps_podcast_indexes_and_complete_episode_content(): void
    {
        $entry = array_merge($this->baseDetail(), [
            'summary' => 'Summary',
            'description' => '<p>Description</p>',
            'video_url' => 'https://youtube.test/watch?v=one',
            'spotify_url' => 'https://open.spotify.com/episode/one',
            'thumbnail_url' => 'https://images.test/one.jpg',
            'transcript' => '<p>Transcript</p>',
            'published_at' => '2026-08-01 12:00:00',
        ]);
        $mapper = new EditorialDataMapper;

        $index = $mapper->mapPodcastIndex(['entries' => [
            'data' => [$entry],
            'total' => 1,
            'per_page' => 10,
            'current_page' => 1,
            'last_page' => 1,
            'has_more_pages' => false,
        ]]);
        $episode = $mapper->mapPodcast($entry);

        $this->assertInstanceOf(PodcastIndexData::class, $index);
        $this->assertSame('https://images.test/one.jpg', $index->items[0]->thumbnailUrl);
        $this->assertInstanceOf(PodcastData::class, $episode);
        $this->assertSame('<p>Transcript</p>', $episode->transcriptHtml);
        $this->assertSame('2026-08-01 12:00:00', $episode->publishedAt);
    }

    #[Test]
    public function it_maps_upcoming_past_and_detail_event_fields(): void
    {
        $upcoming = array_merge($this->baseDetail(), [
            'type' => ['value' => 'Meetup', 'label' => 'Meetup'],
            'date_start' => '2026-09-01',
            'time_start' => '19:00',
            'time_end' => '22:00',
            'location' => 'Utrecht',
            'address' => 'Stationsplein 1',
            'signup_link' => 'https://meetup.test/event',
            'content' => [['__typename' => 'Set_Content_Spacer', 'id' => 'space', 'type' => 'spacer', 'spacer' => 'large']],
        ]);
        $mapper = new EditorialDataMapper;

        $index = $mapper->mapEventIndex([
            'upcoming' => ['data' => [$upcoming]],
            'past' => [
                'data' => [],
                'total' => 24,
                'per_page' => 10,
                'current_page' => 2,
                'from' => 11,
                'to' => 20,
                'last_page' => 3,
                'has_more_pages' => true,
            ],
        ]);
        $event = $mapper->mapEvent($upcoming);

        $this->assertInstanceOf(EventIndexData::class, $index);
        $this->assertSame('2026-09-01', $index->upcoming[0]->dateStart);
        $this->assertSame(2, $index->pagination->currentPage);
        $this->assertTrue($index->pagination->hasMorePages);
        $this->assertInstanceOf(EventData::class, $event);
        $this->assertSame('19:00', $event->timeStart);
        $this->assertSame('large', $event->content[0]->value);
        $this->assertSame('https://meetup.test/event', $event->signupLink);
    }

    /** @return array<string, mixed> */
    private function articleCard(): array
    {
        return array_merge($this->baseDetail(), [
            'category' => ['value' => 'Netwerk', 'label' => 'Netwerk'],
            'date' => '2026-08-01 00:00:00',
            'introduction' => '<p>Introduction</p>',
        ]);
    }

    /** @return array<string, mixed> */
    private function insight(): array
    {
        return array_merge($this->articleCard(), [
            'content' => [
                ['__typename' => 'BardText', 'type' => 'text', 'text' => '<h2>Heading</h2>'],
                ['__typename' => 'Set_Content_Video', 'id' => 'video', 'type' => 'video', 'video' => 'https://youtube.test/video'],
            ],
            'author_name' => 'Ada',
            'author_role' => 'Engineer',
            'author_bio' => '<p>Bio</p>',
            'author_image' => $this->asset('ada.jpg'),
            'author_link' => ['url' => 'https://ada.test', 'title' => 'Ada'],
        ]);
    }

    /** @return array<string, mixed> */
    private function baseDetail(): array
    {
        return [
            'id' => 'entry-id',
            'title' => 'Editorial entry',
            'slug' => 'editorial-entry',
            'url' => '/editorial-entry',
            'uri' => '/editorial-entry',
            'featured_image' => $this->asset('featured.jpg'),
            'introduction' => '<p>Introduction</p>',
            'meta_title' => 'SEO title',
            'meta_description' => 'SEO description',
            'meta_keywords' => 'laravel,php',
            'call_to_action' => [
                'id' => 'cta-id',
                'title' => 'Join us',
                'description' => '<p>Become a member</p>',
                'eyebrow' => 'Community',
                'benefits' => ['Knowledge', 'Network'],
                'link' => ['url' => '/word-lid', 'title' => 'Join'],
                'link_2' => null,
                'theme' => ['value' => 'red', 'label' => 'Rood'],
                'button_text' => 'Word lid',
                'button_style' => ['value' => 'primary', 'label' => 'Primair'],
                'button_text_2' => null,
                'button_style_2' => null,
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function asset(string $path): array
    {
        return [
            'id' => "container::{$path}",
            'url' => "/assets/{$path}",
            'permalink' => "https://example.test/assets/{$path}",
            'path' => $path,
            'extension' => pathinfo($path, PATHINFO_EXTENSION),
            'width' => 1200,
            'height' => 800,
            'focus_css' => '50% 50%',
            'alt' => 'Alternative text',
        ];
    }
}
