<?php

namespace HelgeSverre\Extractor\Extraction\Builtins;

use HelgeSverre\Extractor\ContactDto;
use HelgeSverre\Extractor\Extraction\Concerns\ExpectsJson;
use HelgeSverre\Extractor\Extraction\Extractor;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Exceptions\InvalidDataClass;

class Contacts extends Extractor
{
    use ExpectsJson;

//    use HasDto;
//    use TrimsInput;

    public function dataClass(): string
    {
        return ContactDto::class;
    }


    public function process(string $response): mixed
    {
//        $dataClass = match (true) {
//            /** @psalm-suppress UndefinedThisPropertyFetch */
//            property_exists($this, 'dataClass') => $this->dataClass,
//            method_exists($this, 'dataClass') => $this->dataClass(),
//            default => null,
//        };
//
//        if (!is_a($dataClass, BaseData::class, true)) {
//            throw InvalidDataClass::create($dataClass);
//        }

        return ContactDto::collection(json_decode($response, true));
    }
}
