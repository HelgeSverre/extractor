<?php

declare(strict_types=1);

namespace HelgeSverre\Extractor\Text\Loaders;

use HelgeSverre\Extractor\Contracts\TextLoader;
use HelgeSverre\Extractor\Text\TextContent;
use HelgeSverre\Extractor\Text\Utils;

class Html implements TextLoader
{
    public function load(mixed $data): ?TextContent
    {
        return new TextContent(
            Utils::cleanHtml($data)
        );
    }
}
