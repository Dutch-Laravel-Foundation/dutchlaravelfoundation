<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Seo\OpenGraphImage;
use App\Services\Seo\SeoMetadata;
use Illuminate\Http\Request;
use Statamic\Entries\Entry;
use Statamic\Facades\Entry as EntryRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class OpenGraphImageController extends Controller
{
    public function __invoke(Request $request, string $entry, SeoMetadata $seo, OpenGraphImage $image): BinaryFileResponse
    {
        $found = EntryRepository::find($entry);

        // Only published, public entries get a card, so drafts do not leak their title.
        abort_unless($found instanceof Entry && $found->published() && ! $found->private(), 404);

        $card = $seo->openGraphCard($found);

        abort_if($card === null, 404);

        // The URL carries the fingerprint, so a matching version never changes.
        $cacheControl = $request->query('v') === $card->fingerprint()
            ? 'public, max-age=31536000, immutable'
            : 'public, max-age=3600';

        return response()->file($image->path($card), [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => $cacheControl,
        ]);
    }
}
