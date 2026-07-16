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

    /** @return array<string, mixed> */
    private function parseYaml(string $path): array
    {
        $this->assertFileExists($path);

        return Yaml::parseFile($path);
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
