<?php

declare(strict_types=1);

namespace App\Content\Editorial;

interface EditorialRepository
{
    /** @return array<string, mixed> */
    public function paginateInsights(int $page = 1, ?string $category = null): array;

    /** @return array<string, mixed>|null */
    public function findInsightByUri(string $uri): ?array;

    /** @return array<string, mixed> */
    public function paginateKnowledge(int $page = 1, ?string $category = null): array;

    /** @return array<string, mixed>|null */
    public function findKnowledgeByUri(string $uri): ?array;

    /** @return array<string, mixed> */
    public function paginatePodcasts(int $page = 1): array;

    /** @return array<string, mixed>|null */
    public function findPodcastByUri(string $uri): ?array;

    /** @return array<string, mixed> */
    public function paginateEvents(int $page = 1): array;

    /** @return array<string, mixed>|null */
    public function findEventByUri(string $uri): ?array;
}
