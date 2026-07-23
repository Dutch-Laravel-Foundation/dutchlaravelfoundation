<?php

declare(strict_types=1);

namespace App\Tags;

use App\Services\Seo\SeoMetadata as SeoMetadataService;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Entry as EntryRepository;
use Statamic\Tags\Tags;

final class SeoMetadata extends Tags
{
    protected static $handle = 'seo_metadata';

    public function title(): string
    {
        return $this->service()->title($this->entry());
    }

    public function description(): string
    {
        return $this->service()->description($this->entry());
    }

    public function canonical(): string
    {
        return $this->service()->canonicalUrl($this->entry());
    }

    public function openGraphType(): string
    {
        return $this->service()->openGraphType($this->entry());
    }

    public function jsonLd(): string
    {
        return $this->service()->jsonLd($this->entry());
    }

    private function service(): SeoMetadataService
    {
        return app(SeoMetadataService::class);
    }

    private function entry(): ?Entry
    {
        $id = $this->context->value('id');

        if (is_string($id) && $id !== '') {
            return EntryRepository::find($id);
        }

        return $this->service()->currentEntry();
    }
}
