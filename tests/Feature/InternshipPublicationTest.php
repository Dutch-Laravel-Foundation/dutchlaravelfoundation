<?php

declare(strict_types=1);

use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades\Entry;

/** @var list<EntryContract> $created */
$created = [];

beforeEach(function (): void {
    config([
        'cache.stores.response_cache_testing' => ['driver' => 'array'],
        'responsecache.cache.store' => 'response_cache_testing',
    ]);
});

afterEach(function () use (&$created): void {
    foreach (array_reverse($created) as $entry) {
        Entry::find($entry->id())?->delete();
    }

    $created = [];
});

$make = function (string $collection, string $slug, array $data = [], bool $published = true) use (&$created): EntryContract {
    $entry = Entry::make()
        ->collection($collection)
        ->slug($slug)
        ->data(['title' => $slug, ...$data])
        ->published($published);

    $created[] = $entry;
    $entry->save();

    return $entry;
};

test('an internship without a member is saved unpublished', function () use ($make) {
    $internship = $make('internships', 'test-internship-without-member');

    expect(Entry::find($internship->id())->published())->toBeFalse();
});

test('an internship of an unpublished member is saved unpublished', function () use ($make) {
    $member = $make('members', 'test-unpublished-member', published: false);
    $internship = $make('internships', 'test-internship-of-unpublished-member', ['member' => $member->id()]);

    expect(Entry::find($internship->id())->published())->toBeFalse();
});

test('an internship of a published member stays published', function () use ($make) {
    $member = $make('members', 'test-published-member');
    $internship = $make('internships', 'test-internship-of-published-member', ['member' => $member->id()]);

    expect(Entry::find($internship->id())->published())->toBeTrue();
});

test('unpublishing a member unpublishes its internships', function () use ($make) {
    $member = $make('members', 'test-member-to-unpublish');
    $internship = $make('internships', 'test-internship-to-unpublish', ['member' => $member->id()]);

    $member->published(false)->save();

    expect(Entry::find($internship->id())->published())->toBeFalse();
});

test('deleting a member unpublishes its internships', function () use ($make) {
    $member = $make('members', 'test-member-to-delete');
    $internship = $make('internships', 'test-internship-of-deleted-member', ['member' => $member->id()]);

    $member->delete();

    expect(Entry::find($internship->id())->published())->toBeFalse();
});
