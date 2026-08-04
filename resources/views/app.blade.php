<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @php
        $seoMetadata = resolve(\App\Services\Seo\SeoMetadata::class);
        $entry = $seoMetadata->currentEntry();
        $title = $seoMetadata->title($entry);
        $description = $seoMetadata->description($entry);
        $canonicalUrl = $seoMetadata->canonicalUrl($entry);
        $socialImageUrl = $seoMetadata->socialImageUrl($entry);
    @endphp
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <script @cspNonce>
            if (
                /iPad|iPhone|iPod/.test(navigator.userAgent) ||
                (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)
            ) {
                document.querySelector('meta[name="viewport"]')?.setAttribute(
                    'content',
                    'width=device-width, initial-scale=1, maximum-scale=1',
                );
            }
        </script>

        <script @cspNonce>
            try {
                const consent = JSON.parse(localStorage.getItem('dlf_tracking_consent') ?? 'null');

                if (
                    consent?.version === 1 &&
                    ['accepted', 'rejected'].includes(consent.choice)
                ) {
                    document.documentElement.dataset.dlfTrackingConsentResolved = 'true';
                }
            } catch {
                // An unavailable storage API is treated as undecided consent.
            }
        </script>

        @viteReactRefresh
        @vite(['resources/css/tailwind.css', 'resources/js/app.tsx'])
        <x-inertia::head>
            <title>{{ $title }}</title>
            <meta name="description" content="{{ $description }}" data-inertia="description">
            @if ($keywords = $seoMetadata->keywords($entry))
                <meta name="keywords" content="{{ $keywords }}" data-inertia="keywords">
            @endif
            <link rel="canonical" href="{{ $canonicalUrl }}" data-inertia="canonical">
            <meta property="og:title" content="{{ $title }}" data-inertia="og-title">
            <meta property="og:type" content="{{ $seoMetadata->openGraphType($entry) }}" data-inertia="og-type">
            <meta property="og:url" content="{{ $canonicalUrl }}" data-inertia="og-url">
            <meta property="og:description" content="{{ $description }}" data-inertia="og-description">
            <meta property="og:image" content="{{ $socialImageUrl }}" data-inertia="og-image">
            <meta name="twitter:card" content="summary_large_image" data-inertia="twitter-card">
            <meta name="twitter:title" content="{{ $title }}" data-inertia="twitter-title">
            <meta name="twitter:description" content="{{ $description }}" data-inertia="twitter-description">
            <meta name="twitter:image" content="{{ $socialImageUrl }}" data-inertia="twitter-image">
            <script type="application/ld+json" @cspNonce data-inertia="structured-data">{!! $seoMetadata->jsonLd($entry) !!}</script>
            <meta name="msapplication-TileColor" content="#ffffff" data-inertia="ms-tile-color">
            <meta name="theme-color" content="#ffffff" data-inertia="theme-color">
            <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" data-inertia="apple-touch-icon">
            <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png" data-inertia="favicon-32">
            <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png" data-inertia="favicon-16">
            <link rel="manifest" href="/site.webmanifest" data-inertia="manifest">
            <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5" data-inertia="mask-icon">
        </x-inertia::head>
    </head>
    <body class="dlf-site font-sans leading-normal bg-white text-primary-text">
        <x-inertia::app />
    </body>
</html>
