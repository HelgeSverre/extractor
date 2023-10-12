<?php

namespace HelgeSverre\Extractor\TextLoader;

use HelgeSverre\Extractor\Contracts\TextLoader;
use HelgeSverre\Extractor\TextContent;
use HelgeSverre\Extractor\TextUtils;
use Illuminate\Support\Facades\Http;

class Web implements TextLoader
{
    public function load(mixed $data): ?TextContent
    {
        return new TextContent(
            content: TextUtils::cleanHtml(Http::get($data)->throw()->body()),
        );
    }
}
