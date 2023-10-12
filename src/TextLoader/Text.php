<?php

namespace HelgeSverre\Extractor\TextLoader;

use HelgeSverre\Extractor\Contracts\TextLoader;
use HelgeSverre\Extractor\TextContent;

class Text implements TextLoader
{
    public function load(mixed $data): ?TextContent
    {
        return new TextContent($data);
    }
}
