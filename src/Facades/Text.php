<?php

declare(strict_types=1);

namespace HelgeSverre\Extractor\Facades;

use HelgeSverre\Extractor\Text\Factory;
use Illuminate\Support\Facades\Facade;

/**
 * @see Factory
 */
class Text extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Factory::class;
    }
}
