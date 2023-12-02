<?php

namespace HelgeSverre\Extractor\Extraction;

class AutoExtractor extends Extractor
{
    public function viewName(): string
    {
        return 'extractor::auto-fields';
    }
}
