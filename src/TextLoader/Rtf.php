<?php

namespace HelgeSverre\Extractor\TextLoader;

use HelgeSverre\Extractor\Contracts\TextLoader;
use HelgeSverre\Extractor\TextContent;
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
