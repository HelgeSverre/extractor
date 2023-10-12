<?php

namespace HelgeSverre\Extractor\TextLoader;

use HelgeSverre\Extractor\Contracts\TextLoader;
use HelgeSverre\Extractor\TextContent;
use HelgeSverre\Extractor\TextUtils;

class Html implements TextLoader
{
    public function load(mixed $data): ?TextContent
    {
        return new TextContent(
            TextUtils::cleanHtml($data)
        );
    }
}
