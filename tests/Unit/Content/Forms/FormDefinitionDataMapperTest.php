<?php

declare(strict_types=1);
use App\Content\Forms\FormDefinitionDataMapper;

it('maps graphql form metadata to an explicit dto', function () {
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

    expect($form->handle)->toBe('contact');
    expect($form->action)->toBe('/!/forms/contact');
    expect($form->rules['email'])->toBe(['required', 'email']);
    expect($form->fields[0]->handle)->toBe('email');
    expect($form->fields[0]->config['placeholder'])->toBe('naam@bedrijf.nl');
});
