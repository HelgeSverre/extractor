<?php

namespace HelgeSverre\Extractor\Extractors;

use HelgeSverre\Extractor\Extractors\Concerns\ExtractsJson;
use HelgeSverre\Extractor\Extractors\Concerns\TrimsInput;

class Receipt extends BaseExtractor
{
    use ExtractsJson;
    use TrimsInput;

    public function name(): string
    {
        return 'receipt';
    }
}
