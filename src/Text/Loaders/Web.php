<?php

declare(strict_types=1);

namespace HelgeSverre\Extractor\Text\Loaders;

use HelgeSverre\Extractor\Contracts\TextLoader;
use HelgeSverre\Extractor\Text\TextContent;
use HelgeSverre\Extractor\Text\Utils;
use Illuminate\Support\Facades\Http;

class Web implements TextLoader
{
    public function load(mixed $data): ?TextContent
    {
        return new TextContent(
            content: Utils::cleanHtml(Http::get($data)->throw()->body()),
        );
    }
}
