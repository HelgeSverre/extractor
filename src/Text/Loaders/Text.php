<?php

namespace HelgeSverre\Extractor\Text\Loaders;

use HelgeSverre\Extractor\Contracts\TextLoader;
use HelgeSverre\Extractor\Text\TextContent;

class Text implements TextLoader
{
    public function load(mixed $data): ?TextContent
    {
        return new TextContent($data);
    }
}
