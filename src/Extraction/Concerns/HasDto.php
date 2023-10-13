<?php

namespace HelgeSverre\Extractor\Extraction\Concerns;

use HelgeSverre\Extractor\Extraction\Extractor;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\WithData;

/**
 * @mixin Extractor
 */
trait HasDto
{
    use WithData;

    abstract public function dataClass(): BaseData;
}
