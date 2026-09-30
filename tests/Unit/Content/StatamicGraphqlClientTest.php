<?php

declare(strict_types=1);

namespace Tests\Unit\Content;

use App\Content\Exceptions\GraphqlQueryFailed;
use App\Content\Graphql\StatamicGraphqlClient;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Rebing\GraphQL\GraphQL;
use Statamic\GraphQL\TypeRegistrar;

final class StatamicGraphqlClientTest extends TestCase
{
    #[Test]
    public function it_returns_only_the_data_from_an_in_process_query(): void
    {
        $graphql = $this->createMock(GraphQL::class);
        $request = Request::create('/');
        $typeRegistrar = $this->createMock(TypeRegistrar::class);
        $typeRegistrar->expects($this->once())->method('register');
        $graphql->expects($this->once())
            ->method('query')
            ->with('query Site { ping }', ['locale' => 'nl'], [
                'schema' => 'default',
                'context' => $request,
            ])
            ->willReturn([
                'data' => ['ping' => 'pong'],
            ]);

        $client = new StatamicGraphqlClient($graphql, $request, $typeRegistrar);

        $this->assertSame(
            ['ping' => 'pong'],
            $client->query('query Site { ping }', ['locale' => 'nl']),
        );
    }

    #[Test]
    public function it_rejects_graphql_responses_containing_errors(): void
    {
        $graphql = $this->createStub(GraphQL::class);
        $request = Request::create('/');
        $typeRegistrar = $this->createStub(TypeRegistrar::class);
        $graphql->method('query')->willReturn([
            'data' => null,
            'errors' => [
                ['message' => 'Unknown field'],
                ['message' => 'Invalid variable'],
            ],
        ]);

        $client = new StatamicGraphqlClient($graphql, $request, $typeRegistrar);

        $this->expectException(GraphqlQueryFailed::class);
        $this->expectExceptionMessage('Unknown field; Invalid variable');

        $client->query('query Broken { nope }');
    }
}
