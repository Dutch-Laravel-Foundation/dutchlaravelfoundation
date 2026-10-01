<?php

declare(strict_types=1);
use App\Content\Graphql\GraphqlClient;
use App\Content\SiteShell\StatamicSiteShellRepository;

it('s query matches the live statamic graphql schema', function () {
    $repository = new StatamicSiteShellRepository(
        $this->app->make(GraphqlClient::class),
    );

    $response = $repository->fetch();

    expect($response['organization']['title'])->toBe('Gegevens Dutch Laravel Foundation');
    expect($response['seo']['meta_title'])->toBe('Dutch Laravel Foundation');
    expect($response['mainNavigation']['handle'])->toBe('main');
    expect($response['legalNavigation']['handle'])->toBe('legal');
    expect($response['members']['data'])->not->toBeEmpty();
    expect($response['socials']['data'])->not->toBeEmpty();
    expect($response['defaultCta']['id'])->toBe('ee5d33de-9a24-4860-92dd-3503740b62af');
    expect($response['newsletter']['handle'])->toBe('newsletter');
    expect($response['newsletter']['fields'])->not->toBeEmpty();
});
