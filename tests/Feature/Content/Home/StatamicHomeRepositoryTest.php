<?php

declare(strict_types=1);
use App\Content\Graphql\GraphqlClient;
use App\Content\Home\StatamicHomeRepository;

it('s query matches the real statamic graphql schema', function () {
    $repository = new StatamicHomeRepository($this->app->make(GraphqlClient::class));

    $content = $repository->get();

    expect($content['latestInsight']['data'][0]['__typename'])->toBe('Entry_Insights_Insights');
    expect($content['latestKnowledge']['data'][0]['__typename'])->toBe('Entry_Knowledge_Knowledge');
    expect($content['highlightedInsight']['data'][0]['__typename'])->toBe('Entry_Insights_Insights');
    expect($content['partners']['data'])->not->toBeEmpty();
    expect($content['clients']['data'])->not->toBeEmpty();
    expect($content['latestInsight']['data'][0])->toHaveKey('featured_image');
    expect($content['partners']['data'][0])->toHaveKey('logo');
    expect($content['clients']['data'][0])->toHaveKey('logo');
});
