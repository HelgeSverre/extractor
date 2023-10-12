<?php

namespace HelgeSverre\Extractor\Extractors\Concerns;

use HelgeSverre\Extractor\TextContent;
use Illuminate\Support\Str;

trait TrimsInput
{
    public function preprocess(TextContent|string $input): string
    {
        return Str::of($input)->squish()->trim();
    }
}
