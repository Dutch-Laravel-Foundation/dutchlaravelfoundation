<?php

declare(strict_types=1);

use App\Content\Community\CommunityDataMapper;
use App\Data\Community\CaseData;
use App\Data\Community\CaseIndexData;
use App\Data\Community\ContentBlockData;
use App\Data\Community\InternshipData;
use App\Data\Community\InternshipIndexData;
use App\Data\Community\LarabellesData;
use App\Data\Community\MemberData;
use App\Data\Community\MemberIndexData;

describe(CommunityDataMapper::class, function (): void {
    beforeEach(function (): void {
        $this->mapper = new CommunityDataMapper;
    });

    it('maps case index cards and complete case detail content', function (): void {
        $case = communityCaseFixture();
        $index = $this->mapper->mapCaseIndex([
            'page' => communityPageFixture('Cases', '/cases'),
            'entries' => ['data' => [$case]],
        ]);
        $detail = $this->mapper->mapCase($case);

        expect($index)->toBeInstanceOf(CaseIndexData::class)
            ->and($index->items[0]->displayTitle)->toBe('A longer case title')
            ->and($index->items[0]->member?->title)->toBe('Example Agency')
            ->and($index->page->callToAction?->buttonText)->toBe('Start je project')
            ->and($detail)->toBeInstanceOf(CaseData::class)
            ->and($detail->content[0])->toBeInstanceOf(ContentBlockData::class)
            ->and($detail->content[0]->html)->toBe('<h2>Techniek</h2>')
            ->and($detail->content[1]->asset?->focusCss)->toBe('45% 55%')
            ->and($detail->client?->title)->toBe('Example Client')
            ->and($detail->seo->description)->toBe('Case SEO description');
    });

    it('maps sortable member filters and complete member detail relations', function (): void {
        $member = communityMemberFixture();
        $index = $this->mapper->mapMemberIndex([
            'page' => communityPageFixture('Onze leden', '/leden'),
            'entries' => ['data' => [
                array_merge($member, ['id' => 'member-b', 'title' => 'Zebra', 'province' => ['value' => 'utrecht', 'label' => 'Utrecht']]),
                $member,
            ]],
        ]);
        $detail = $this->mapper->mapMember([
            'member' => $member,
            'internships' => ['data' => [communityInternshipFixture()]],
            'cases' => ['data' => [communityCaseFixture()]],
        ]);

        expect($index)->toBeInstanceOf(MemberIndexData::class)
            ->and($index->items[0]->title)->toBe('Example Agency')
            ->and($index->filters->types)->toBe(['Bedrijf'])
            ->and($index->filters->employeeRanges)->toBe(['11-50'])
            ->and($index->filters->provinces)->toBe(['Noord-Brabant', 'Utrecht'])
            ->and($detail)->toBeInstanceOf(MemberData::class)
            ->and($detail->descriptionHtml)->toBe('<p>Member description</p>')
            ->and($detail->internshipContact?->email)->toBe('internships@example.test')
            ->and($detail->internships[0]->applyUrl)->toBe('https://example.test/internships')
            ->and($detail->cases[0]->client?->title)->toBe('Example Client');
    });

    it('maps stagebank intro filters, detail links and larabelles bard content', function (): void {
        $internship = communityInternshipFixture();
        $stagebank = $this->mapper->mapInternshipIndex([
            'page' => array_merge(communityPageFixture('Stagebank', '/stagebank'), [
                'content' => [[
                    '__typename' => 'Set_Content_DoubleColumn',
                    'id' => 'intro',
                    'type' => 'double_column',
                    'heading' => '<h1>Vind je stage</h1>',
                    'left' => [['__typename' => 'BardText', 'type' => 'text', 'text' => '<p>Intro links</p>']],
                    'right' => [],
                ]],
            ]),
            'entries' => ['data' => [$internship]],
        ]);
        $detail = $this->mapper->mapInternship($internship);
        $larabelles = $this->mapper->mapLarabelles(array_merge(
            communityPageFixture('Larabelles', '/larabelles'),
            ['content' => [['__typename' => 'BardText', 'type' => 'text', 'text' => '<h2>Onze missie</h2>']]],
        ));

        expect($stagebank)->toBeInstanceOf(InternshipIndexData::class)
            ->and($stagebank->page->content[0]->columns?->left[0]->html)->toBe('<p>Intro links</p>')
            ->and($stagebank->filters->provinces)->toBe(['Noord-Brabant'])
            ->and($stagebank->filters->hasSbb)->toBeTrue()
            ->and($detail)->toBeInstanceOf(InternshipData::class)
            ->and($detail->applyUrl)->toBe('https://example.test/internships')
            ->and($detail->member->internshipContact?->phone)->toBe('+31 20 123 4567')
            ->and($larabelles)->toBeInstanceOf(LarabellesData::class)
            ->and($larabelles->page->content[0]->html)->toBe('<h2>Onze missie</h2>')
            ->and($larabelles->page->seo->description)->toBe('Page SEO description');
    });

    it('returns null for missing detail entries and ignores malformed list items', function (): void {
        expect($this->mapper->mapCase(null))->toBeNull()
            ->and($this->mapper->mapMember(['member' => null]))->toBeNull()
            ->and($this->mapper->mapInternship(null))->toBeNull()
            ->and($this->mapper->mapLarabelles(null))->toBeNull()
            ->and($this->mapper->mapMemberIndex([
                'page' => communityPageFixture('Members', '/leden'),
                'entries' => ['data' => [null, 'invalid']],
            ])->items)->toBe([]);
    });
});

function communityPageFixture(string $title, string $uri): array
{
    return [
        'id' => "page-{$title}",
        'title' => $title,
        'slug' => ltrim($uri, '/'),
        'url' => $uri,
        'uri' => $uri,
        'template' => 'templates/community/index',
        'content' => [],
        'call_to_action' => [
            'id' => 'cta-id',
            'title' => 'Laat je project bouwen',
            'description' => '<p>Vertel ons over je project.</p>',
            'eyebrow' => 'Aan de slag',
            'benefits' => ['Vrijblijvend', 'Gericht advies'],
            'link' => ['url' => '/aanvraag', 'title' => 'Aanvraag'],
            'link_2' => null,
            'theme' => ['value' => 'red', 'label' => 'Rood'],
            'button_text' => 'Start je project',
            'button_style' => ['value' => 'primary', 'label' => 'Primair'],
            'button_text_2' => null,
            'button_style_2' => null,
        ],
        'meta_title' => 'Page SEO title',
        'meta_description' => 'Page SEO description',
        'meta_keywords' => 'laravel,community',
    ];
}

function communityCaseFixture(): array
{
    return [
        'id' => 'case-id',
        'title' => 'Case title',
        'title_long' => 'A longer case title',
        'slug' => 'case-title',
        'url' => '/cases/case-title',
        'uri' => '/cases/case-title',
        'date' => '2026-07-01',
        'introduction' => '<p>Case introduction</p>',
        'featured_image' => communityAssetFixture('case.jpg'),
        'content' => [
            ['__typename' => 'BardText', 'type' => 'text', 'text' => '<h2>Techniek</h2>'],
            ['__typename' => 'Set_Content_Image', 'id' => 'image-id', 'type' => 'image', 'image' => communityAssetFixture('detail.jpg')],
        ],
        'member' => communityMemberFixture(),
        'client' => [
            'id' => 'client-id',
            'title' => 'Example Client',
            'slug' => 'example-client',
            'url' => '/clients/example-client',
            'uri' => '/clients/example-client',
            'logo' => communityAssetFixture('client.svg'),
        ],
        'meta_title' => 'Case SEO title',
        'meta_description' => 'Case SEO description',
        'meta_keywords' => 'laravel,case',
    ];
}

function communityMemberFixture(): array
{
    return [
        'id' => 'member-id',
        'title' => 'Example Agency',
        'slug' => 'example-agency',
        'url' => '/leden/example-agency',
        'uri' => '/leden/example-agency',
        'description' => '<p>Member description</p>',
        'logo' => communityAssetFixture('member.svg'),
        'founding_partner' => true,
        'type' => ['value' => 'bedrijf', 'label' => 'Bedrijf'],
        'employees' => ['value' => '11-50', 'label' => '11-50'],
        'sbb' => true,
        'city' => 'Eindhoven',
        'province' => ['value' => 'noord-brabant', 'label' => 'Noord-Brabant'],
        'email' => 'hello@example.test',
        'phone' => '+31 40 123 4567',
        'website' => 'example.test',
        'recruitment_website' => 'jobs.example.test',
        'video' => 'https://youtube.test/watch?v=example',
        'internship_contact_name' => 'Ada Lovelace',
        'internship_contact_email' => 'internships@example.test',
        'internship_contact_phone' => '+31 20 123 4567',
        'meta_title' => 'Member SEO title',
        'meta_description' => 'Member SEO description',
        'meta_keywords' => 'laravel,agency',
    ];
}

function communityInternshipFixture(): array
{
    return [
        'id' => 'internship-id',
        'title' => 'Software engineering internship',
        'slug' => 'software-engineering',
        'url' => '/stagebank/software-engineering',
        'uri' => '/stagebank/software-engineering',
        'description' => '<p>Internship description</p>',
        'apply_url' => ['url' => 'https://example.test/internships', 'title' => 'Apply'],
        'member' => communityMemberFixture(),
        'meta_title' => 'Internship SEO title',
        'meta_description' => 'Internship SEO description',
        'meta_keywords' => 'laravel,internship',
    ];
}

function communityAssetFixture(string $path): array
{
    return [
        'id' => "container::{$path}",
        'url' => "/assets/{$path}",
        'permalink' => "https://example.test/assets/{$path}",
        'path' => $path,
        'extension' => pathinfo($path, PATHINFO_EXTENSION),
        'width' => 1200,
        'height' => 800,
        'focus_css' => '45% 55%',
        'alt' => 'Alternative text',
    ];
}
