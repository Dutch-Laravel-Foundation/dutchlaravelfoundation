<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Testing\TestResponse;
use Tests\TestCase;

final class MemberDetailPageTest extends TestCase
{
    public function test_member_with_internships_renders_its_detail_page(): void
    {
        $this->inertia('/leden/besite')
            ->assertOk()
            ->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Community/MembersShow')
            ->assertJsonPath('props.community.title', 'Besite')
            ->assertJsonPath('props.community.logo.url', '/assets/uploads/members/logo-besite.svg')
            ->assertJsonPath('props.community.internships.0.title', 'Besite');
    }

    public function test_member_detail_marks_member_navigation_item_active(): void
    {
        $response = $this->inertia('/leden/pionect');

        $response->assertOk();

        $navigation = $response->json('props.site.navigation.main');

        $this->assertIsArray($navigation);

        $members = array_find(
            $navigation,
            static fn (mixed $item): bool => is_array($item) && ($item['url'] ?? null) === '/leden',
        );

        $this->assertIsArray($members);
        $this->assertFalse($members['isCurrent']);
        $this->assertTrue($members['isAncestor']);
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
