<?php

namespace HelgeSverre\Extractor\Extraction\Builtins;

use HelgeSverre\Extractor\Extraction\Extractor;

class DataClass extends Extractor
{
    public function viewName(): string
    {
        return 'extractor::fields';
    }
}
