<?php

use HelgeSverre\Extractor\Extraction\Builtins\Receipt;
use Illuminate\Validation\ValidationException;

describe('HasValidation Trait', function () {
    describe('boot', function () {
        it('registers a processor during boot', function () {
            $extractor = new Receipt;

            $reflection = new ReflectionClass($extractor);
            $property = $reflection->getProperty('processors');
            $property->setAccessible(true);
            $processors = $property->getValue($extractor);

            $validationProcessor = collect($processors)->firstWhere('priority', 100);

            expect($validationProcessor)->not->toBeNull();
            expect($validationProcessor['callback'])->toBeCallable();
        });
    });

    describe('validation', function () {
        it('validates data against rules and returns valid data', function () {
            $extractor = new Receipt;

            $reflection = new ReflectionClass($extractor);
            $property = $reflection->getProperty('processors');
            $property->setAccessible(true);
            $processors = $property->getValue($extractor);

            $validationProcessor = collect($processors)->firstWhere('priority', 100);

            $validData = [
                'orderRef' => 'ORD-123',
                'date' => '2025-01-15',
                'taxAmount' => 10.00,
                'totalAmount' => 100.00,
                'currency' => 'USD',
                'merchant' => [
                    'name' => 'Test Store',
                    'vatId' => null,
                    'address' => '123 Main St',
                ],
                'lineItems' => [
                    ['text' => 'Item 1', 'sku' => 'SKU1', 'qty' => 1, 'price' => 100.00],
                ],
            ];

            $result = ($validationProcessor['callback'])($validData);

            expect($result)->toHaveKey('date', '2025-01-15');
            expect($result)->toHaveKey('totalAmount', 100.00);
        });

        it('throws ValidationException when throwsOnValidationFailure is true and data is invalid', function () {
            $extractor = new Receipt;

            $reflection = new ReflectionClass($extractor);
            $property = $reflection->getProperty('processors');
            $property->setAccessible(true);
            $processors = $property->getValue($extractor);

            $validationProcessor = collect($processors)->firstWhere('priority', 100);

            $invalidData = [
                'orderRef' => 'ORD-123',
                'date' => 'not-a-date',
                'totalAmount' => 'not-a-number',
                'merchant' => [],
            ];

            expect(fn () => ($validationProcessor['callback'])($invalidData))
                ->toThrow(ValidationException::class);
        });

        it('returns valid subset when throwsOnValidationFailure is false', function () {
            $extractor = new class extends HelgeSverre\Extractor\Extraction\Extractor
            {
                use HelgeSverre\Extractor\Extraction\Concerns\HasValidation;

                public function rules(): array
                {
                    return [
                        'name' => ['required', 'string'],
                        'email' => ['required', 'email'],
                    ];
                }

                // Default throwsOnValidationFailure() returns false
            };

            $reflection = new ReflectionClass($extractor);
            $property = $reflection->getProperty('processors');
            $property->setAccessible(true);
            $processors = $property->getValue($extractor);

            $validationProcessor = collect($processors)->firstWhere('priority', 100);

            $data = [
                'name' => 'John Doe',
                'email' => 'not-an-email',
            ];

            $result = ($validationProcessor['callback'])($data);

            expect($result)->toHaveKey('name', 'John Doe');
            expect($result)->not->toHaveKey('email');
        });
    });
});
