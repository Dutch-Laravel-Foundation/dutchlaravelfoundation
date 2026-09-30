<?php

declare(strict_types=1);

namespace Tests\Unit\Content\Forms;

use App\Content\Forms\FormDefinitionDataMapper;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FormDefinitionDataMapperTest extends TestCase
{
    #[Test]
    public function it_maps_graphql_form_metadata_to_an_explicit_dto(): void
    {
        $form = (new FormDefinitionDataMapper)->map([
            'handle' => 'contact',
            'title' => 'Contact',
            'honeypot' => 'fax_number',
            'rules' => ['email' => ['required', 'email']],
            'fields' => [[
                'handle' => 'email',
                'type' => 'text',
                'display' => 'E-mailadres',
                'instructions' => 'Vul je e-mailadres in.',
                'width' => 100,
                'if' => [],
                'unless' => [],
                'config' => ['input_type' => 'email', 'placeholder' => 'naam@bedrijf.nl'],
            ]],
        ]);

        $this->assertSame('contact', $form->handle);
        $this->assertSame('/!/forms/contact', $form->action);
        $this->assertSame(['required', 'email'], $form->rules['email']);
        $this->assertSame('email', $form->fields[0]->handle);
        $this->assertSame('naam@bedrijf.nl', $form->fields[0]->config['placeholder']);
    }
}
