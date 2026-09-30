<?php

declare(strict_types=1);

namespace App\Content\Forms;

use App\Content\Graphql\GraphqlClient;

final readonly class StatamicFormsRepository implements FormsRepository
{
    private const string FORM_DEFINITION = <<<'GRAPHQL'
        query FormDefinition($handle: String!) {
            form(handle: $handle) {
                handle
                title
                honeypot
                rules
                fields {
                    handle
                    type
                    display
                    instructions
                    width
                    if
                    unless
                    config
                }
            }
        }
        GRAPHQL;

    public function __construct(private GraphqlClient $client) {}

    public function find(string $handle): ?array
    {
        $response = $this->client->query(self::FORM_DEFINITION, ['handle' => $handle]);
        $form = $response['form'] ?? null;

        return is_array($form) ? $form : null;
    }
}
