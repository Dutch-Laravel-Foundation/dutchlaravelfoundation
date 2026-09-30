<?php

declare(strict_types=1);

namespace Tests\Unit\Content\Forms;

use App\Content\Forms\StatamicFormsRepository;
use App\Content\Graphql\GraphqlClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StatamicFormsRepositoryTest extends TestCase
{
    #[Test]
    public function it_fetches_a_form_definition_through_graphql(): void
    {
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

        $this->assertSame(
            ['handle' => 'contact'],
            (new StatamicFormsRepository($client))->find('contact'),
        );
    }
}
