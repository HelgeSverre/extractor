<?php

namespace HelgeSverre\Extractor\Extraction\Builtins;

use HelgeSverre\Extractor\Extraction\Extractor;

class Simple extends Extractor
{
    public function viewName(): string
    {
        return $this->config('view');
    }
}
