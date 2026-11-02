<?php

namespace App\Services\AppleDb;

use Carbon\CarbonInterface;

/**
 * Enrichment payload returned by AppleDbEnrichment::lookup(). Every
 * field is nullable because the upstream catalog may know some
 * attributes about a given productType and not others (new releases
 * are typically imageless for a few weeks, obsolescence dates are
 * only populated once Apple formally classifies a model as vintage
 * or obsolete, etc).
 *
 * imageFilename is the filename portion of a PNG already saved under
 * the public disk at models/, keyed by (productType, colorKey). The
 * caller assigns this to AssetModel::image.
 */
readonly class AppleDbRecord
{
    public function __construct(
        public ?string $imageFilename = null,
        public ?string $marketingName = null,
        public ?string $modelNumber = null,
        public ?CarbonInterface $releaseDate = null,
        public ?CarbonInterface $discontinuedDate = null,
    ) {}
}
