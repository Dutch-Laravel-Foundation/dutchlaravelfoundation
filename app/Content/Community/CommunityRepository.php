<?php

declare(strict_types=1);

namespace App\Content\Community;

interface CommunityRepository
{
    /** @return array<string, mixed> */
    public function caseIndex(): array;

    /** @return array<string, mixed>|null */
    public function findCaseByUri(string $uri): ?array;

    /** @return array<string, mixed> */
    public function memberIndex(): array;

    /** @return array<string, mixed> */
    public function findMemberByUri(string $uri): array;

    /** @return array<string, mixed> */
    public function internshipIndex(): array;

    /** @return array<string, mixed>|null */
    public function findInternshipByUri(string $uri): ?array;

    /** @return array<string, mixed>|null */
    public function findLarabellesByUri(string $uri = '/larabelles'): ?array;
}
