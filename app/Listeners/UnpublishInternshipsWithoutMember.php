<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Support\Arr;
use Statamic\Entries\Entry;
use Statamic\Events\EntryDeleted;
use Statamic\Events\EntrySaved;
use Statamic\Events\EntrySaving;
use Statamic\Facades\Entry as Entries;

/**
 * An internship may only be published while its member is published.
 */
final class UnpublishInternshipsWithoutMember
{
    public function handle(EntrySaving|EntrySaved|EntryDeleted $event): void
    {
        $entry = $event->entry;

        if (! $entry instanceof Entry) {
            return;
        }

        if ($event instanceof EntrySaving) {
            if ($entry->collectionHandle() === 'internships' && $entry->published() && ! $this->hasPublishedMember($entry)) {
                $entry->published(false);
            }

            return;
        }

        if ($entry->collectionHandle() !== 'members') {
            return;
        }

        if ($event instanceof EntrySaved && $entry->published()) {
            return;
        }

        Entries::whereCollection('internships')
            ->filter(fn (mixed $internship): bool => $internship instanceof Entry
                && $internship->published()
                && $this->memberId($internship) === $entry->id())
            ->each(fn (Entry $internship) => $internship->published(false)->save());
    }

    private function memberId(Entry $internship): ?string
    {
        $memberId = Arr::first(Arr::wrap($internship->value('member')));

        return is_string($memberId) && $memberId !== '' ? $memberId : null;
    }

    private function hasPublishedMember(Entry $internship): bool
    {
        $memberId = $this->memberId($internship);

        if ($memberId === null) {
            return false;
        }

        $member = Entries::find($memberId);

        return $member instanceof Entry && $member->published();
    }
}
