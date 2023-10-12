<?php

namespace HelgeSverre\Extractor\Facades;

use HelgeSverre\Extractor\TextLoaderFactory;
use Illuminate\Support\Facades\Facade;

/**
 * @see TextLoaderFactory
 */
class Text extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TextLoaderFactory::class;
    }
}
