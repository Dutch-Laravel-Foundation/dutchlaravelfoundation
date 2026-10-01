<?php

declare(strict_types=1);
test('every community family is served by its inertia component', function () {
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
        $response = $this->withHeaders(inertiaHeaders())->get($uri);

        $response->assertOk();
        $response->assertHeader('X-Inertia', 'true');
        $response->assertJsonPath('component', $component);
        $response->assertJsonStructure(['props' => [$prop, 'site']]);
    }
});
/** @return array<string, string> */
