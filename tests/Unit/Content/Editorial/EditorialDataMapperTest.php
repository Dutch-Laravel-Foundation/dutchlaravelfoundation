<?php

declare(strict_types=1);
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

it('maps paginated article cards and pagination metadata', function () {
    $index = (new EditorialDataMapper)->mapArticleIndex([
        'entries' => [
            'data' => [articleCard()],
            'total' => 21,
            'per_page' => 10,
            'current_page' => 2,
            'last_page' => 3,
            'has_more_pages' => true,
        ],
    ]);

    expect($index->items[0])->toBeInstanceOf(ArticleCardData::class);
    expect($index->items[0]->category)->toBe('Netwerk');
    expect($index->items[0]->featuredImage?->url)->toBe('/assets/featured.jpg');
    expect($index->pagination->currentPage)->toBe(2);
    expect($index->pagination->lastPage)->toBe(3);
    expect($index->pagination->hasMorePages)->toBeTrue();
});
it('maps an insight with rendered bard blocks author cta and seo', function () {
    $insight = (new EditorialDataMapper)->mapInsight(insight());

    expect($insight)->toBeInstanceOf(InsightData::class);
    expect($insight->introduction)->toBe('<p>Introduction</p>');
    expect($insight->content[0])->toBeInstanceOf(ContentBlockData::class);
    expect($insight->content[0]->type)->toBe('text');
    expect($insight->content[0]->html)->toBe('<h2>Heading</h2>');
    expect($insight->content[1]->value)->toBe('https://youtube.test/video');
    expect($insight->author)->toBeInstanceOf(AuthorData::class);
    expect($insight->author?->name)->toBe('Ada');
    expect($insight->callToAction?->title)->toBe('Join us');
    expect($insight->seo->title)->toBe('SEO title');
});
it('maps a knowledge article with ordered related authors', function () {
    $entry = baseDetail();
    $entry['content'] = '<h2>Knowledge</h2>';
    $entry['authors'] = [
        [
            'id' => 'author-1',
            'title' => 'Taylor',
            'job_title' => 'Developer',
            'description' => '<p>Biography</p>',
            'photo' => editorialAsset('author.jpg'),
            'photo_url' => null,
            'linkedin_url' => ['url' => 'https://linkedin.test/taylor', 'title' => null],
            'website_url' => ['url' => 'https://example.test', 'title' => 'Website'],
        ],
    ];

    $knowledge = (new EditorialDataMapper)->mapKnowledge($entry);

    expect($knowledge)->toBeInstanceOf(KnowledgeData::class);
    expect($knowledge->contentHtml)->toBe('<h2>Knowledge</h2>');
    expect($knowledge->authors[0]->name)->toBe('Taylor');
    expect($knowledge->authors[0]->linkedinUrl)->toBe('https://linkedin.test/taylor');
});
it('maps podcast indexes and complete episode content', function () {
    $entry = array_merge(baseDetail(), [
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

    expect($index)->toBeInstanceOf(PodcastIndexData::class);
    expect($index->items[0]->thumbnailUrl)->toBe('https://images.test/one.jpg');
    expect($episode)->toBeInstanceOf(PodcastData::class);
    expect($episode->transcriptHtml)->toBe('<p>Transcript</p>');
    expect($episode->publishedAt)->toBe('2026-08-01 12:00:00');
});
it('maps upcoming past and detail event fields', function () {
    $upcoming = array_merge(baseDetail(), [
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

    expect($index)->toBeInstanceOf(EventIndexData::class);
    expect($index->upcoming[0]->dateStart)->toBe('2026-09-01');
    expect($index->pagination->currentPage)->toBe(2);
    expect($index->pagination->hasMorePages)->toBeTrue();
    expect($event)->toBeInstanceOf(EventData::class);
    expect($event->timeStart)->toBe('19:00');
    expect($event->content[0]->value)->toBe('large');
    expect($event->signupLink)->toBe('https://meetup.test/event');
});
/** @return array<string, mixed> */
function articleCard(): array
{
    return array_merge(baseDetail(), [
        'category' => ['value' => 'Netwerk', 'label' => 'Netwerk'],
        'date' => '2026-08-01 00:00:00',
        'introduction' => '<p>Introduction</p>',
    ]);
}
/** @return array<string, mixed> */
function insight(): array
{
    return array_merge(articleCard(), [
        'content' => [
            ['__typename' => 'BardText', 'type' => 'text', 'text' => '<h2>Heading</h2>'],
            ['__typename' => 'Set_Content_Video', 'id' => 'video', 'type' => 'video', 'video' => 'https://youtube.test/video'],
        ],
        'author_name' => 'Ada',
        'author_role' => 'Engineer',
        'author_bio' => '<p>Bio</p>',
        'author_image' => editorialAsset('ada.jpg'),
        'author_link' => ['url' => 'https://ada.test', 'title' => 'Ada'],
    ]);
}
/** @return array<string, mixed> */
function baseDetail(): array
{
    return [
        'id' => 'entry-id',
        'title' => 'Editorial entry',
        'slug' => 'editorial-entry',
        'url' => '/editorial-entry',
        'uri' => '/editorial-entry',
        'featured_image' => editorialAsset('featured.jpg'),
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
function editorialAsset(string $path): array
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
