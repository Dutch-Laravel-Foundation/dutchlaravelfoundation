<?php

declare(strict_types=1);
test('stagebank overview uses updated filter heading', function () {
    $response = inertiaVisit('/stagebank');

    $response->assertOk()
        ->assertJsonPath('component', 'Community/InternshipsIndex')
        ->assertJsonStructure(['props' => ['community' => ['items', 'filters']]]);

    $page = file_get_contents(resource_path('js/pages/Community/InternshipsIndex.tsx'));

    $this->assertNotFalse($page);
    $this->assertStringContainsString('title="Wij helpen je zoeken!"', $page);
    $this->assertStringNotContainsString('Kunnen wij je helpen zoeken?', $page);
});
test('internship detail uses updated apply button label', function () {
    $response = inertiaVisit('/stagebank/qlic');

    $response->assertOk()
        ->assertJsonPath('component', 'Community/InternshipsShow')
        ->assertJsonPath('props.community.applyUrl', 'https://www.qlic.nl/vacatures/stage-backend-developer/');

    $page = file_get_contents(resource_path('js/pages/Community/InternshipsShow.tsx'));

    $this->assertNotFalse($page);
    $this->assertStringContainsString('Bekijk stage vacatures', $page);
    $this->assertStringNotContainsString('Solliciteren', $page);
});
test('internship detail merges company information into the header', function () {
    $response = inertiaVisit('/stagebank/superscanner');

    $response->assertOk()
        ->assertJsonPath('component', 'Community/InternshipsShow')
        ->assertJsonPath('props.community.member.website', 'superscanner.nl')
        ->assertJsonPath('props.community.member.city', 'Haarlem')
        ->assertJsonPath('props.community.member.internshipContact.name', 'Andries Mooij');

    $page = file_get_contents(resource_path('js/pages/Community/InternshipsShow.tsx'));

    $this->assertNotFalse($page);
    $this->assertStringContainsString('Stage contactpersoon', $page);
    $this->assertStringNotContainsString('Stagebedrijf', $page);
});
test('internship tiles do not render duplicate company name line', function () {
    $component = file_get_contents(resource_path('js/components/community-react/DirectoryCards.tsx'));

    $this->assertNotFalse($component);
    $internshipCard = strstr($component, 'export function InternshipCard');

    expect($internshipCard)->toBeString();
    $this->assertStringContainsString('{internship.title}', $internshipCard);
    $this->assertStringNotContainsString('dlf-member-card__name">{member.title}', $internshipCard);
});
test('stagebank overview renders member logos', function () {
    $response = inertiaVisit('/stagebank');

    $response->assertOk();

    $items = $response->json('props.community.items');

    expect($items)->toBeArray();

    $logos = array_map(
        static fn (array $item): mixed => $item['member']['logo']['url'] ?? null,
        $items,
    );

    expect($logos)->toContain('/assets/uploads/members/ux-logo.svg');
});
test('internship detail renders the member logo', function () {
    inertiaVisit('/stagebank/ux')
        ->assertOk()
        ->assertJsonPath('props.community.member.logo.url', '/assets/uploads/members/ux-logo.svg');
});
