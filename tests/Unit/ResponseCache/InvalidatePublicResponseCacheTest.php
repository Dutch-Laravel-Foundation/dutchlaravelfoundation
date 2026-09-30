<?php

declare(strict_types=1);

use App\Listeners\InvalidatePublicResponseCache;
use App\ResponseCache\PublicResponseCacheTags;
use Spatie\ResponseCache\ResponseCache;
use Statamic\Entries\Entry;
use Statamic\Events\EntrySaved;

it('invalidates only the changed entry and its dependent overview', function (): void {
    $entry = $this->createMock(Entry::class);
    $entry->method('collectionHandle')->willReturn('insights');
    $entry->method('root')->willReturn($entry);
    $entry->method('id')->willReturn('entry-id');
    $entry->method('uri')->willReturn('/nieuws/nieuw-artikel');
    $entry->method('slug')->willReturn('nieuw-artikel');
    $entry->method('getOriginal')->willReturn('oud-artikel');

    $responseCache = $this->createMock(ResponseCache::class);
    $responseCache->expects($this->once())
        ->method('clear')
        ->with($this->identicalTo([
            'entry:/nieuws/nieuw-artikel',
            'entry:/nieuws/oud-artikel',
            'overview:insights',
        ]))
        ->willReturn(true);

    $listener = new InvalidatePublicResponseCache(
        $responseCache,
        new PublicResponseCacheTags,
    );

    $listener->handle(new EntrySaved($entry));
});

it('keeps full invalidation for content changes without safe entry-level dependencies', function (): void {
    $responseCache = $this->createMock(ResponseCache::class);
    $responseCache->expects($this->once())
        ->method('clear')
        ->with()
        ->willReturn(true);

    $listener = new InvalidatePublicResponseCache(
        $responseCache,
        new PublicResponseCacheTags,
    );

    $listener->handle(new stdClass);
});
