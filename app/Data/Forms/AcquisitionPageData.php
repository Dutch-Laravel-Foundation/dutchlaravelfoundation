<?php

declare(strict_types=1);

namespace App\Data\Forms;

use App\Data\PublicPages\PublicPageData;
use Spatie\LaravelData\Data;

final class AcquisitionPageData extends Data
{
    public function __construct(
        public readonly PublicPageData $page,
        public readonly ?FormDefinitionData $form,
        public readonly FormSubmissionStateData $submission,
    ) {}
}
