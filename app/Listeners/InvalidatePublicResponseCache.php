<?php

declare(strict_types=1);

namespace App\Listeners;

use App\ResponseCache\PublicResponseCacheTags;
use Spatie\ResponseCache\ResponseCache;
use Statamic\Events\EntryDeleted;
use Statamic\Events\EntrySaved;

final readonly class InvalidatePublicResponseCache
{
    public function __construct(
        private ResponseCache $responseCache,
        private PublicResponseCacheTags $tags,
    ) {}

    public function handle(object $event): void
    {
        if ($event instanceof EntrySaved || $event instanceof EntryDeleted) {
            $tags = $this->tags->forEntry($event->entry);

            if ($tags !== []) {
                $this->responseCache->clear($tags);

                return;
            }
        }

        $this->responseCache->clear();
    }
}
