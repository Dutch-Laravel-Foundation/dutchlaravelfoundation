<?php

declare(strict_types=1);

namespace App\Data\SiteShell;

use App\Data\SeoData;
use Spatie\LaravelData\Data;

final class SiteShellData extends Data
{
    public function __construct(
        public readonly OrganizationData $organization,
        public readonly SeoData $seo,
        public readonly OpenGraphData $openGraph,
        public readonly NavigationData $navigation,
        public readonly FooterData $footer,
        public readonly ?CtaData $defaultCta,
        public readonly ?NewsletterFormData $newsletter,
    ) {}
}
