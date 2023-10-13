<?php

namespace HelgeSverre\Extractor\Text\Loaders;

use HelgeSverre\Extractor\Contracts\TextLoader;
use HelgeSverre\Extractor\Text\TextContent;
use Jstewmc\Rtf\Document;

class Rtf implements TextLoader
{
    public function load(mixed $data): ?TextContent
    {
        $document = new Document($data);
        $text = $document->getRoot()->toText();

        return new TextContent($text);
    }
}
