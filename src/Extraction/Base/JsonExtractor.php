<?php

namespace HelgeSverre\Extractor\Extraction\Base;

use HelgeSverre\Extractor\Extraction\Concerns\ExpectsJson;
use HelgeSverre\Extractor\Extraction\Concerns\TrimsInput;
use HelgeSverre\Extractor\Extraction\Extractor;

abstract class JsonExtractor extends Extractor
{
    use ExpectsJson;
    use TrimsInput;
}
