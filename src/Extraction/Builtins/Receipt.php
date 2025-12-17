<?php

declare(strict_types=1);

namespace HelgeSverre\Extractor\Extraction\Builtins;

use HelgeSverre\Extractor\Extraction\Concerns\HasValidation;
use HelgeSverre\Extractor\Extraction\Extractor;

class Receipt extends Extractor
{
    use HasValidation;

    public function rules(): array
    {
        return [
            'orderRef' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            'taxAmount' => ['nullable', 'numeric'],
            'totalAmount' => ['required', 'numeric'],
            'currency' => ['nullable', 'string', 'size:3'],

            'merchant.name' => ['required', 'string'],
            'merchant.vatId' => ['nullable', 'string'],
            'merchant.address' => ['nullable', 'string'],

            'lineItems.*.text' => ['required', 'string'],
            'lineItems.*.sku' => ['nullable'],
            'lineItems.*.qty' => ['nullable', 'numeric'],
            'lineItems.*.price' => ['nullable', 'numeric'],
        ];
    }

    public function throwsOnValidationFailure()
    {
        return true;
    }
}
