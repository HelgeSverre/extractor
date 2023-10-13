<?php

namespace HelgeSverre\Extractor\Contracts;

use HelgeSverre\Extractor\Text\TextContent;

interface TextLoader
{
    public function load(mixed $data): ?TextContent;
}
