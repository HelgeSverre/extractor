<?php

namespace HelgeSverre\Extractor\Contracts;

use HelgeSverre\Extractor\Extraction\Extractor;
use HelgeSverre\Extractor\Text\TextContent;

interface Engine
{
    public function run(
        Extractor $extractor,
        TextContent|string $input,
        string $model,
        int $maxTokens,
        float $temperature,
    ): mixed;
}
