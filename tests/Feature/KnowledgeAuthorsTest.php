<?php

declare(strict_types=1);

namespace Tests\Feature;

use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

class KnowledgeAuthorsTest extends TestCase
{
    public function testAuthorsCollectionIsCmsOnly(): void
    {
        $collection = $this->parseYaml(base_path('content/collections/authors.yaml'));

        $this->assertSame('Auteurs', $collection['title'] ?? null);
        $this->assertArrayNotHasKey('route', $collection);
        $this->assertArrayNotHasKey('template', $collection);
    }

    public function testKnowledgeBlueprintUsesReusableAuthors(): void
    {
        $authorBlueprint = $this->parseYaml(base_path('resources/blueprints/collections/authors/author.yaml'));
        $knowledgeBlueprint = $this->parseYaml(base_path('resources/blueprints/collections/knowledge/knowledge.yaml'));
        $authorFields = $this->fieldsByHandle($authorBlueprint);
        $knowledgeFields = $this->fieldsByHandle($knowledgeBlueprint);

        foreach (['title', 'job_title', 'photo', 'description', 'linkedin_url', 'website_url'] as $handle) {
            $this->assertArrayHasKey($handle, $authorFields);
        }

        $this->assertSame('entries', $knowledgeFields['authors']['type'] ?? null);
        $this->assertSame(['authors'], $knowledgeFields['authors']['collections'] ?? null);
        $this->assertTrue($knowledgeFields['authors']['reorderable'] ?? false);
        $this->assertArrayNotHasKey('max_items', $knowledgeFields['authors']);

        foreach (['author_name', 'author_role', 'author_bio', 'author_image', 'author_link'] as $legacyHandle) {
            $this->assertArrayNotHasKey($legacyHandle, $knowledgeFields);
        }
    }

    public function testBobKosseIsMigratedToTheAuthorsCollection(): void
    {
        $author = $this->parseFrontMatter(base_path('content/collections/authors/bob-kosse.md'));
        $article = $this->parseFrontMatter(base_path('content/collections/knowledge/2026-07-01-2200.het-belang-van-toegankelijke-websites.md'));

        $this->assertSame('fe71f8d9-3bfe-4f29-af8b-c1726e2b4849', $author['id'] ?? null);
        $this->assertSame('author', $author['blueprint'] ?? null);
        $this->assertSame('Bob Kosse', $author['title'] ?? null);
        $this->assertSame('Onafhankelijk Laravel-ontwikkelaar', $author['job_title'] ?? null);
        $this->assertSame('introductie-afbeelding-bob-kosse.png', $author['photo'] ?? null);
        $this->assertSame('https://www.ikverstajeniet.nl', $author['website_url'] ?? null);
        $this->assertSame(['fe71f8d9-3bfe-4f29-af8b-c1726e2b4849'], $article['authors'] ?? null);

        foreach (['author_name', 'author_role', 'author_bio', 'author_image', 'author_link'] as $legacyHandle) {
            $this->assertArrayNotHasKey($legacyHandle, $article);
        }
    }

    /** @return array<string, mixed> */
    private function parseYaml(string $path): array
    {
        $this->assertFileExists($path);

        return Yaml::parseFile($path);
    }

    /** @return array<string, mixed> */
    private function parseFrontMatter(string $path): array
    {
        $this->assertFileExists($path);

        $contents = file_get_contents($path);
        $this->assertIsString($contents);
        $this->assertSame(1, preg_match('/^---\R(.*?)\R---/s', $contents, $matches));

        return Yaml::parse($matches[1]);
    }

    /**
     * @param array<string, mixed> $node
     * @return array<string, array<string, mixed>>
     */
    private function fieldsByHandle(array $node): array
    {
        $fields = [];

        foreach ($node as $key => $value) {
            if ($key === 'handle' && is_string($value) && isset($node['field']) && is_array($node['field'])) {
                $fields[$value] = $node['field'];
            }

            if (is_array($value)) {
                $fields = array_merge($fields, $this->fieldsByHandle($value));
            }
        }

        return $fields;
    }
}
