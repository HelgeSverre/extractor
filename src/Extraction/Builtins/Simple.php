<?php

namespace HelgeSverre\Extractor\Extraction\Builtins;

use HelgeSverre\Extractor\Extraction\Extractor;
use InvalidArgumentException;

class Simple extends Extractor
{
    public function viewName(): string
    {
        return $this->config('view') ?? throw new InvalidArgumentException('No view provided');
    }
}
