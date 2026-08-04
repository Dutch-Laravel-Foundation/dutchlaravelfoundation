<?php

declare(strict_types=1);

use App\Content\Community\CommunityDataMapper;
use App\Content\Community\StatamicCommunityRepository;
use App\Content\Graphql\GraphqlClient;
use App\Data\Community\CaseData;
use App\Data\Community\InternshipData;
use App\Data\Community\LarabellesData;
use App\Data\Community\MemberData;
use Tests\TestCase;

uses(TestCase::class);

describe(StatamicCommunityRepository::class, function (): void {
    beforeEach(function (): void {
        $this->repository = new StatamicCommunityRepository($this->app->make(GraphqlClient::class));
        $this->mapper = new CommunityDataMapper;
    });

    it('executes every community index query against the real schema', function (): void {
        $cases = $this->mapper->mapCaseIndex($this->repository->caseIndex());
        $members = $this->mapper->mapMemberIndex($this->repository->memberIndex());
        $internships = $this->mapper->mapInternshipIndex($this->repository->internshipIndex());

        expect($cases->items)->not->toBeEmpty()
            ->and($cases->page->seo->description)->not->toBeNull()
            ->and($members->items)->not->toBeEmpty()
            ->and($members->filters->provinces)->not->toBeEmpty()
            ->and($internships->items)->not->toBeEmpty()
            ->and($internships->page->content)->not->toBeEmpty();
    });

    it('preserves complete community detail relationships and bard content', function (): void {
        $case = $this->mapper->mapCase($this->repository->findCaseByUri('/cases/de-verbouwcalculator'));
        $member = $this->mapper->mapMember($this->repository->findMemberByUri('/leden/besite'));
        $internship = $this->mapper->mapInternship($this->repository->findInternshipByUri('/stagebank/besite'));
        $larabelles = $this->mapper->mapLarabelles($this->repository->findLarabellesByUri('/larabelles'));

        expect($case)->toBeInstanceOf(CaseData::class)
            ->and($case->content)->not->toBeEmpty()
            ->and($case->member)->not->toBeNull()
            ->and($case->client)->not->toBeNull()
            ->and($member)->toBeInstanceOf(MemberData::class)
            ->and($member->logo)->not->toBeNull()
            ->and($member->internships)->not->toBeEmpty()
            ->and($internship)->toBeInstanceOf(InternshipData::class)
            ->and($internship->member->internshipContact)->not->toBeNull()
            ->and($larabelles)->toBeInstanceOf(LarabellesData::class)
            ->and($larabelles->page->content)->not->toBeEmpty()
            ->and($larabelles->page->callToAction)->not->toBeNull();
    });
});
