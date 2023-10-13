<?php

namespace HelgeSverre\Extractor\Extraction\Base;

use HelgeSverre\Extractor\Extraction\Extractor;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\WithData;

abstract class DtoExtractor extends Extractor
{
    use WithData;

    abstract public function dataClass(): BaseData;
}
