<?php

declare(strict_types=1);

namespace App\Content\Graphql;

use App\Content\Exceptions\GraphqlQueryFailed;
use Illuminate\Http\Request;
use Rebing\GraphQL\GraphQL;

final readonly class StatamicGraphqlClient implements GraphqlClient
{
    public function __construct(
        private GraphQL $graphql,
        private Request $request,
    ) {}

    public function query(string $document, array $variables = []): array
    {
        $response = $this->graphql->query($document, $variables, [
            'schema' => 'default',
            'context' => $this->request,
        ]);

        if ($response['errors'] ?? []) {
            $messages = array_map(
                static fn (mixed $error): string => is_array($error)
                    ? (string) ($error['message'] ?? 'Unknown GraphQL error')
                    : 'Unknown GraphQL error',
                $response['errors'],
            );

            throw new GraphqlQueryFailed(implode('; ', $messages));
        }

        $data = $response['data'] ?? null;

        if (! is_array($data)) {
            throw new GraphqlQueryFailed('The GraphQL response did not contain data.');
        }

        return $data;
    }
}
