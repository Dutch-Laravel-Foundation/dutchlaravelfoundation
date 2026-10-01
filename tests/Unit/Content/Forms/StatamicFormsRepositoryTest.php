<?php

declare(strict_types=1);
use App\Content\Forms\StatamicFormsRepository;
use App\Content\Graphql\GraphqlClient;

it('fetches a form definition through graphql', function () {
    $client = $this->createMock(GraphqlClient::class);
    $client->expects($this->once())
        ->method('query')
        ->with(
            $this->callback(static fn (string $query): bool => str_contains($query, 'form(handle: $handle)')
                && str_contains($query, 'fields {')
                && str_contains($query, 'config')),
            ['handle' => 'contact'],
        )
        ->willReturn(['form' => ['handle' => 'contact']]);

    expect((new StatamicFormsRepository($client))->find('contact'))->toBe(['handle' => 'contact']);
});
