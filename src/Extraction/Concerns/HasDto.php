<?php

namespace HelgeSverre\Extractor\Extraction\Concerns;

use HelgeSverre\Extractor\Extraction\Extractor;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Exceptions\InvalidDataClass;

/**
 * @mixin Extractor
 */
trait HasDto
{
    abstract public function dataClass(): string;

    public function handle(string $response): mixed
    {
        $dataClass = match (true) {
            /** @psalm-suppress UndefinedThisPropertyFetch */
            property_exists($this, 'dataClass') => $this->dataClass,
            method_exists($this, 'dataClass') => $this->dataClass(),
            default => null,
        };

        if (!is_a($dataClass, BaseData::class, true)) {
            throw InvalidDataClass::create($dataClass);
        }

        return $dataClass::from($response);
    }

    public function bootTrimsInput()
    {
        $this->registerProcessor($this->handle(...));
    }
}
