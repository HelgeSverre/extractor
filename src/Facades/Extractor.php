<?php

declare(strict_types=1);

namespace HelgeSverre\Extractor\Facades;

use HelgeSverre\Extractor\ExtractorManager;
use Illuminate\Support\Facades\Facade;

/**
 * @see ExtractorManager
 */
class Extractor extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ExtractorManager::class;
    }
}
