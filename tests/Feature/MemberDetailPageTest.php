<?php

declare(strict_types=1);
test('member with internships renders its detail page', function () {
    inertiaVisit('/leden/besite')
        ->assertOk()
        ->assertHeader('X-Inertia', 'true')
        ->assertJsonPath('component', 'Community/MembersShow')
        ->assertJsonPath('props.community.title', 'Besite')
        ->assertJsonPath('props.community.logo.url', '/assets/uploads/members/logo-besite.svg')
        ->assertJsonPath('props.community.internships.0.title', 'Besite');
});
test('member detail marks member navigation item active', function () {
    $response = inertiaVisit('/leden/pionect');

    $response->assertOk();

    $navigation = $response->json('props.site.navigation.main');

    expect($navigation)->toBeArray();

    $members = array_find(
        $navigation,
        static fn (mixed $item): bool => is_array($item) && ($item['url'] ?? null) === '/leden',
    );

    expect($members)->toBeArray();
    expect($members['isCurrent'])->toBeFalse();
    expect($members['isAncestor'])->toBeTrue();
});
