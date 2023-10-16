<?php

namespace HelgeSverre\Extractor\Extraction\Concerns;

use HelgeSverre\Extractor\Extraction\Extractor;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * @mixin Extractor
 */
trait HasValidation
{
    abstract public function rules(): array;

    public function throwsOnValidationFailure(): bool
    {
        return false;
    }

    public function bootHasValidation(): void
    {
        $this->registerProcessor(function ($data) {
            $validator = Validator::make($data, $this->rules());

            if ($validator->fails() && $this->throwsOnValidationFailure()) {
                throw new ValidationException($validator);
            }

            return $validator->valid();
        }, 100);
    }
}
