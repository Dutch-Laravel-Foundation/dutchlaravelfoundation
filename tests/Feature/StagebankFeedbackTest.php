<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Testing\TestResponse;
use Tests\TestCase;

final class StagebankFeedbackTest extends TestCase
{
    public function test_stagebank_overview_uses_updated_filter_heading(): void
    {
        $response = $this->inertia('/stagebank');

        $response->assertOk()
            ->assertJsonPath('component', 'Community/InternshipsIndex')
            ->assertJsonStructure(['props' => ['community' => ['items', 'filters']]]);

        $page = file_get_contents(resource_path('js/pages/Community/InternshipsIndex.tsx'));

        $this->assertNotFalse($page);
        $this->assertStringContainsString('title="Wij helpen je zoeken!"', $page);
        $this->assertStringNotContainsString('Kunnen wij je helpen zoeken?', $page);
    }

    public function test_internship_detail_uses_updated_apply_button_label(): void
    {
        $response = $this->inertia('/stagebank/qlic');

        $response->assertOk()
            ->assertJsonPath('component', 'Community/InternshipsShow')
            ->assertJsonPath('props.community.applyUrl', 'https://www.qlic.nl/vacatures/stage-backend-developer/');

        $page = file_get_contents(resource_path('js/pages/Community/InternshipsShow.tsx'));

        $this->assertNotFalse($page);
        $this->assertStringContainsString('Bekijk stage vacatures', $page);
        $this->assertStringNotContainsString('Solliciteren', $page);
    }

    public function test_internship_detail_merges_company_information_into_the_header(): void
    {
        $response = $this->inertia('/stagebank/superscanner');

        $response->assertOk()
            ->assertJsonPath('component', 'Community/InternshipsShow')
            ->assertJsonPath('props.community.member.website', 'superscanner.nl')
            ->assertJsonPath('props.community.member.city', 'Haarlem')
            ->assertJsonPath('props.community.member.internshipContact.name', 'Andries Mooij');

        $page = file_get_contents(resource_path('js/pages/Community/InternshipsShow.tsx'));

        $this->assertNotFalse($page);
        $this->assertStringContainsString('Stage contactpersoon', $page);
        $this->assertStringNotContainsString('Stagebedrijf', $page);
    }

    public function test_internship_tiles_do_not_render_duplicate_company_name_line(): void
    {
        $component = file_get_contents(resource_path('js/components/community-react/DirectoryCards.tsx'));

        $this->assertNotFalse($component);
        $internshipCard = strstr($component, 'export function InternshipCard');

        $this->assertIsString($internshipCard);
        $this->assertStringContainsString('{internship.title}', $internshipCard);
        $this->assertStringNotContainsString('dlf-member-card__name">{member.title}', $internshipCard);
    }

    public function test_stagebank_overview_renders_member_logos(): void
    {
        $response = $this->inertia('/stagebank');

        $response->assertOk();

        $items = $response->json('props.community.items');

        $this->assertIsArray($items);

        $logos = array_map(
            static fn (array $item): mixed => $item['member']['logo']['url'] ?? null,
            $items,
        );

        $this->assertContains('/assets/uploads/members/ux-logo.svg', $logos);
    }

    public function test_internship_detail_renders_the_member_logo(): void
    {
        $this->inertia('/stagebank/ux')
            ->assertOk()
            ->assertJsonPath('props.community.member.logo.url', '/assets/uploads/members/ux-logo.svg');
    }

    private function inertia(string $uri): TestResponse
    {
        return $this->withHeaders([
            'Accept' => 'application/json',
            'X-Inertia' => 'true',
            'X-Inertia-Version' => hash_file('xxh128', public_path('build/manifest.json')),
        ])->get($uri);
    }
}
