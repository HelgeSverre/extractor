<?php

declare(strict_types=1);

namespace HelgeSverre\Extractor\Extraction\Builtins;

use HelgeSverre\Extractor\ContactDto;
use HelgeSverre\Extractor\Extraction\Concerns\HasDto;
use HelgeSverre\Extractor\Extraction\Concerns\HasValidation;
use HelgeSverre\Extractor\Extraction\Extractor;

class Contacts extends Extractor
{
    use HasDto;
    use HasValidation;

    public function rules(): array
    {
        return [
            '*.name' => ['required', 'string'],
            '*.title' => ['required', 'string'],
            '*.email' => ['required', 'email'],
            '*.phone' => ['required'],
        ];
    }

    public function dataClass(): string
    {
        return ContactDto::class;
    }

    public function isCollection(): bool
    {
        return true;
    }
}
