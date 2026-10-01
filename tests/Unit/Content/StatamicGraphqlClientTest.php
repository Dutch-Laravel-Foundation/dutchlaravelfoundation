<?php

declare(strict_types=1);
use App\Content\Exceptions\GraphqlQueryFailed;
use App\Content\Graphql\StatamicGraphqlClient;
use Illuminate\Http\Request;
use Rebing\GraphQL\GraphQL;
use Statamic\GraphQL\TypeRegistrar;

it('returns only the data from an in process query', function () {
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

    expect($client->query('query Site { ping }', ['locale' => 'nl']))->toBe(['ping' => 'pong']);
});
it('rejects graphql responses containing errors', function () {
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
});
