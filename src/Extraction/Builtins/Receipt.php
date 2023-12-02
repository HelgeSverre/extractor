<?php

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
            'totalAmount' => ['nullable', 'numeric'],
            'currency' => ['nullable', 'string', 'size:3'],

            'merchant.name' => ['required', 'string'],
            'merchant.vatId' => ['nullable', 'string'],
            'merchant.address' => ['required', 'string'],

            'lineItems.*.text' => ['required', 'string'],
            'lineItems.*.sku' => ['nullable', 'string'],
            'lineItems.*.qty' => ['required', 'numeric'],
            'lineItems.*.price' => ['required', 'numeric'],
        ];
    }
}
