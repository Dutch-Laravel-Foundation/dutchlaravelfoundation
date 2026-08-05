<?php

declare(strict_types=1);
it('stagebank overview uses updated filter heading', function () {
    $response = $this->get('/stagebank');

    $response->assertOk();
    $response->assertSee('Wij helpen je zoeken!', false);
    $response->assertDontSee('Kunnen wij je helpen zoeken?', false);
});
it('internship detail uses updated apply button label', function () {
    $response = $this->get('/stagebank/qlic');

    $response->assertOk();
    $response->assertSee('Bekijk stage vacatures', false);
    $response->assertDontSee('Solliciteren', false);
});
it('internship detail merges company information into the header', function () {
    $response = $this->get('/stagebank/superscanner');

    $response->assertOk();
    $response->assertSee('href="https://superscanner.nl"', false);
    $response->assertSee('>Locatie<', false);
    $response->assertSee('>Website<', false);
    $response->assertSee('Stage contactpersoon', false);
    $response->assertDontSee('Stagebedrijf', false);
});
it('internship tiles do not render duplicate company name line', function () {
    $template = file_get_contents(resource_path('views/templates/internships/index.antlers.html'));

    $this->assertNotFalse($template);
    $this->assertStringNotContainsString('x-text="item.member_title"', $template);
});
it('stagebank overview renders member logos', function () {
    $this->get('/stagebank')
        ->assertOk()
        ->assertSee('data-logo="/assets/uploads/members/ux-logo.svg"', false);
});
it('internship detail renders the member logo', function () {
    $this->get('/stagebank/ux')
        ->assertOk()
        ->assertSee('<img src="/assets/uploads/members/ux-logo.svg"', false);
});
