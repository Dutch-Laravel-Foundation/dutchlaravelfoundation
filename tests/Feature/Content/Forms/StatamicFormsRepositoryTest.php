<?php

declare(strict_types=1);
use App\Content\Forms\FormDefinitionDataMapper;
use App\Content\Forms\FormsRepository;

test('all react form definitions match the live graphql schema', function () {
    $repository = $this->app->make(FormsRepository::class);

    foreach (['become_member', 'contact', 'sales_funnel'] as $handle) {
        $form = $repository->find($handle);

        expect($form)->not->toBeNull();
        expect($form['handle'])->toBe($handle);
        expect($form['fields'])->not->toBeEmpty();
        expect((new FormDefinitionDataMapper)->map($form)->rules)->not->toBeEmpty();
    }
});
