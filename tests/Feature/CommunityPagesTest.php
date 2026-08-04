<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CommunityPagesTest extends TestCase
{
    #[Test]
    public function every_community_family_is_served_by_its_inertia_component(): void
    {
        $routes = [
            '/cases' => ['Community/CasesIndex', 'community'],
            '/cases/dropday' => ['Community/CasesShow', 'community'],
            '/leden' => ['Community/MembersIndex', 'community'],
            '/leden/adwise' => ['Community/MembersShow', 'community'],
            '/stagebank' => ['Community/InternshipsIndex', 'community'],
            '/stagebank/adwise' => ['Community/InternshipsShow', 'community'],
            '/larabelles' => ['Community/Larabelles', 'community'],
        ];

        foreach ($routes as $uri => [$component, $prop]) {
            $response = $this->withHeaders($this->inertiaHeaders())->get($uri);

            $response->assertOk();
            $response->assertHeader('X-Inertia', 'true');
            $response->assertJsonPath('component', $component);
            $response->assertJsonStructure(['props' => [$prop, 'site']]);
        }
    }

    /** @return array<string, string> */
    private function inertiaHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'X-Inertia' => 'true',
            'X-Inertia-Version' => hash_file('xxh128', public_path('build/manifest.json')),
        ];
    }
}
