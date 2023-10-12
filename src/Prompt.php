<?php

namespace HelgeSverre\Extractor;

use Illuminate\Support\Facades\View;

class Prompt
{
    public static function load(string $filename, array $data = []): string
    {
        return View::make("extractor::{$filename}", $data)->render();
    }
}
