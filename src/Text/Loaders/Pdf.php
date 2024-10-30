<?php

namespace HelgeSverre\Extractor\Text\Loaders;

use HelgeSverre\Extractor\Contracts\TextLoader;
use HelgeSverre\Extractor\Text\TextContent;
use Smalot\PdfParser\Parser;

class Pdf implements TextLoader
{
    public function load(mixed $data): ?TextContent
    {
        $parser = new Parser;
        $parsed = $parser->parseContent($data);
        $text = $parsed->getText();

        return new TextContent($text);
    }
}
