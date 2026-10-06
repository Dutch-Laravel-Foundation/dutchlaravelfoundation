<?php

declare(strict_types=1);

use Illuminate\Testing\TestResponse;
use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->in('Feature', 'Unit');

pest()->tia()
    ->always()
    ->locally()
    ->baselined()
    ->filtered();

/*
 * Helpers shared by several test files.
 */

function inertiaHeaders(): array
{
    return [
        'Accept' => 'application/json',
        'X-Inertia' => 'true',
        'X-Inertia-Version' => hash_file('xxh128', public_path('build/manifest.json')),
    ];
}

function inertiaVisit(string $uri): TestResponse
{
    return test()->withHeaders(inertiaHeaders())->get($uri);
}

function parseFrontMatter(string $path): array
{
    expect($path)->toBeFile();

    $contents = file_get_contents($path);
    expect($contents)->toBeString();
    expect(preg_match('/^---\R(.*?)\R---/s', $contents, $matches))->toBe(1);

    return Yaml::parse($matches[1]);
}

function parseYaml(string $path): array
{
    expect($path)->toBeFile();

    return Yaml::parseFile($path);
}

function fieldsByHandle(array $node): array
{
    $fields = [];

    foreach ($node as $key => $value) {
        if ($key === 'handle' && is_string($value) && isset($node['field']) && is_array($node['field'])) {
            $fields[$value] = $node['field'];
        }

        if (is_array($value)) {
            $fields = array_merge($fields, fieldsByHandle($value));
        }
    }

    return $fields;
}
