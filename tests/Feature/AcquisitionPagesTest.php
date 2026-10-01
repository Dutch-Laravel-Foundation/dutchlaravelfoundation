<?php

declare(strict_types=1);
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

test('every acquisition page is served by its inertia component and dto', function () {
    $routes = [
        '/contact' => ['Forms/Contact', 'contact'],
        '/lid-worden' => ['Forms/BecomeMember', 'become_member'],
        '/aanvraag' => ['Forms/SalesFunnel', 'sales_funnel'],
        '/aanvraag/bedankt' => ['Forms/Thanks', null],
    ];

    foreach ($routes as $uri => [$component, $formHandle]) {
        $response = $this->withHeaders(inertiaHeaders())->get($uri);

        $response->assertOk();
        $response->assertHeader('X-Inertia', 'true');
        $response->assertJsonPath('component', $component);
        $response->assertJsonStructure([
            'props' => [
                'acquisition' => ['page', 'form', 'submission'],
                'site',
            ],
        ]);
        $response->assertJsonPath('props.acquisition.form.handle', $formHandle);
    }
});
it('maps the named statamic form session to the submission dto', function () {
    $errors = new ViewErrorBag;
    $errors->put('form.contact', new MessageBag([
        'email' => ['Vul een geldig e-mailadres in.'],
    ]));

    $response = $this
        ->withSession([
            'errors' => $errors,
            '_old_input' => ['email' => 'ongeldig', '_token' => 'secret', 'address' => ''],
            'form.contact.success' => 'Submission successful.',
        ])
        ->withHeaders(inertiaHeaders())
        ->get('/contact');

    $response->assertOk();
    $response->assertJsonPath('props.acquisition.submission.success', true);
    $response->assertJsonPath(
        'props.acquisition.submission.errors.email',
        'Vul een geldig e-mailadres in.',
    );
    $response->assertJsonPath('props.acquisition.submission.old.email', 'ongeldig');
    $response->assertJsonMissingPath('props.acquisition.submission.old._token');
    $response->assertJsonMissingPath('props.acquisition.submission.old.address');
});
/** @return array<string, string> */
