<?php

declare(strict_types=1);

use App\Content\Community\StatamicCommunityRepository;
use App\Content\Graphql\GraphqlClient;

describe(StatamicCommunityRepository::class, function (): void {
    it('fetches case index and detail content with attribution data', function (): void {
        $client = $this->createMock(GraphqlClient::class);
        $client->expects($this->exactly(2))
            ->method('query')
            ->with(
                $this->callback(fn (string $document): bool => str_contains($document, 'query CaseIndex')
                    ? str_contains($document, 'sort: ["date desc"]')
                        && str_contains($document, 'page: entry')
                        && str_contains($document, 'call_to_action')
                    : str_contains($document, 'query CaseDetail')
                        && str_contains($document, 'content')
                        && str_contains($document, 'client {')
                        && str_contains($document, 'member {')),
                $this->callback(fn (array $variables): bool => $variables['site'] === 'default'
                    && in_array($variables['uri'], ['/cases', '/cases/example'], true)),
            )
            ->willReturnOnConsecutiveCalls(
                ['page' => ['id' => 'cases-page'], 'entries' => ['data' => []]],
                ['entry' => ['id' => 'case-id']],
            );

        $repository = new StatamicCommunityRepository($client);

        expect($repository->caseIndex()['page']['id'])->toBe('cases-page')
            ->and($repository->findCaseByUri('/cases/example')['id'])->toBe('case-id');
    });

    it('fetches member index filters and related detail collections', function (): void {
        $member = ['id' => 'member-id', 'title' => 'Example'];
        $client = $this->createMock(GraphqlClient::class);
        $client->expects($this->exactly(3))
            ->method('query')
            ->willReturnCallback(function (string $document, array $variables) use ($member): array {
                expect($variables['site'])->toBe('default');

                if (str_contains($document, 'query MemberIndex')) {
                    expect($variables['uri'])->toBe('/leden')
                        ->and($document)->toContain('sort: ["title asc"]')
                        ->and($document)->toContain('province { value label }');

                    return ['page' => ['id' => 'members-page'], 'entries' => ['data' => []]];
                }

                if (str_contains($document, 'query MemberDetail')) {
                    expect($variables['uri'])->toBe('/leden/example')
                        ->and($document)->toContain('internship_contact_email')
                        ->and($document)->toContain('recruitment_website');

                    return ['entry' => $member];
                }

                expect($document)->toContain('query MemberRelatedContent')
                    ->and($variables['memberFilter'])->toBe(['member' => ['is' => 'member-id']]);

                return ['internships' => ['data' => []], 'cases' => ['data' => []]];
            });

        $repository = new StatamicCommunityRepository($client);

        expect($repository->memberIndex()['page']['id'])->toBe('members-page')
            ->and($repository->findMemberByUri('/leden/example'))->toMatchArray([
                'member' => $member,
                'internships' => ['data' => []],
                'cases' => ['data' => []],
            ]);
    });

    it('fetches stagebank page data, internships, detail contacts and larabelles content', function (): void {
        $client = $this->createMock(GraphqlClient::class);
        $client->expects($this->exactly(3))
            ->method('query')
            ->willReturnCallback(function (string $document, array $variables): array {
                expect($variables['site'])->toBe('default');

                if (str_contains($document, 'query InternshipIndex')) {
                    expect($variables['uri'])->toBe('/stagebank')
                        ->and($document)->toContain('Set_Content_DoubleColumn')
                        ->and($document)->toContain('apply_url { url title }');

                    return ['page' => ['id' => 'stagebank-page'], 'entries' => ['data' => []]];
                }

                if (str_contains($document, 'query InternshipDetail')) {
                    expect($variables['uri'])->toBe('/stagebank/example')
                        ->and($document)->toContain('internship_contact_name')
                        ->and($document)->toContain('sbb');

                    return ['entry' => ['id' => 'internship-id']];
                }

                expect($variables['uri'])->toBe('/larabelles')
                    ->and($document)->toContain('query LarabellesPage')
                    ->and($document)->toContain('... on BardText');

                return ['entry' => ['id' => 'larabelles-page']];
            });

        $repository = new StatamicCommunityRepository($client);

        expect($repository->internshipIndex()['page']['id'])->toBe('stagebank-page')
            ->and($repository->findInternshipByUri('/stagebank/example')['id'])->toBe('internship-id')
            ->and($repository->findLarabellesByUri('/larabelles')['id'])->toBe('larabelles-page');
    });
});
