<?php

declare(strict_types=1);

namespace Tests\Feature\Agents\Mcp;

use Tests\TestCase;

class ListCollectionToolsTest extends TestCase
{
    /**
     * @return array<int, array{0: string, 1: string}>
     */
    public static function toolCases(): array
    {
        return [
            ['list_events', 'events'],
            ['list_internships', 'internships'],
            ['list_cases', 'cases'],
            ['list_members', 'members'],
            ['list_board', 'board'],
            ['list_partners', 'partners'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('toolCases')]
    public function testToolReturnsJsonArrayForCollection(string $toolName, string $expectedCollection): void
    {
        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => $toolName,
                'arguments' => ['limit' => 5],
            ],
        ]);

        $response->assertOk();
        $items = json_decode((string) $response->json('result.content.0.text'), true);

        $this->assertIsArray($items, "tool {$toolName} should return a JSON array");

        if ($items !== []) {
            $this->assertSame($expectedCollection, $items[0]['collection']);
            $this->assertArrayHasKey('title', $items[0]);
            $this->assertArrayHasKey('url', $items[0]);
        }
    }
}
