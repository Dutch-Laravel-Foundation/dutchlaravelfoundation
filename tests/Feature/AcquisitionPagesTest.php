<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class AcquisitionPagesTest extends TestCase
{
    #[Test]
    public function every_acquisition_page_is_served_by_its_inertia_component_and_dto(): void
    {
        $routes = [
            '/contact' => ['Forms/Contact', 'contact'],
            '/lid-worden' => ['Forms/BecomeMember', 'become_member'],
            '/aanvraag' => ['Forms/SalesFunnel', 'sales_funnel'],
            '/aanvraag/bedankt' => ['Forms/Thanks', null],
        ];

        foreach ($routes as $uri => [$component, $formHandle]) {
            $response = $this->withHeaders($this->inertiaHeaders())->get($uri);

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
    }

    #[Test]
    public function it_maps_the_named_statamic_form_session_to_the_submission_dto(): void
    {
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
            ->withHeaders($this->inertiaHeaders())
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
