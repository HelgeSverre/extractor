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

    public function isCollection(): bool
    {
        return false;
    }

    public function bootHasDto(): void
    {
        $this->registerProcessor(function ($response): mixed {
            $dataClass = match (true) {
                /** @psalm-suppress UndefinedThisPropertyFetch */
                property_exists($this, 'dataClass') => $this->dataClass,
                method_exists($this, 'dataClass') => $this->dataClass(),
                default => null,
            };

            if (! is_a($dataClass, BaseData::class, true)) {
                throw InvalidDataClass::create($dataClass);
            }

            if (! is_array($response)) {
                $response = json_decode($response, true);
            }

            return $this->isCollection()
                ? $dataClass::collection($response)
                : $dataClass::from($response);
        });
    }
}
