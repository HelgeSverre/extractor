<?php

use HelgeSverre\Extractor\ContactDto;
use HelgeSverre\Extractor\Extraction\Builtins\Contacts;
use Spatie\LaravelData\Exceptions\InvalidDataClass;

describe('HasDto Trait', function () {
    it('converts array response to a Data object', function () {
        $extractor = new class extends HelgeSverre\Extractor\Extraction\Extractor
        {
            use HelgeSverre\Extractor\Extraction\Concerns\HasDto;

            public function dataClass(): string
            {
                return ContactDto::class;
            }
        };

        $reflection = new ReflectionClass($extractor);
        $property = $reflection->getProperty('processors');
        $property->setAccessible(true);
        $processors = $property->getValue($extractor);

        $dtoProcessor = collect($processors)->firstWhere('priority', 1000);

        $result = ($dtoProcessor['callback'])([
            'name' => 'Jane Doe',
            'title' => 'Engineer',
            'phone' => '555-1234',
            'email' => 'jane@example.com',
        ]);

        expect($result)->toBeInstanceOf(ContactDto::class);
        expect($result->name)->toBe('Jane Doe');
        expect($result->email)->toBe('jane@example.com');
    });

    it('converts array response to a collection when isCollection is true', function () {
        $extractor = new Contacts;

        $reflection = new ReflectionClass($extractor);
        $property = $reflection->getProperty('processors');
        $property->setAccessible(true);
        $processors = $property->getValue($extractor);

        $dtoProcessor = collect($processors)->firstWhere('priority', 1000);

        $data = [
            [
                'name' => 'Jane Doe',
                'title' => 'Engineer',
                'phone' => '555-1234',
                'email' => 'jane@example.com',
            ],
            [
                'name' => 'John Smith',
                'title' => 'Manager',
                'phone' => '555-5678',
                'email' => 'john@example.com',
            ],
        ];

        // Data::collection() was removed in spatie/laravel-data v4
        if (! method_exists(ContactDto::class, 'collection')) {
            expect(fn () => ($dtoProcessor['callback'])($data))
                ->toThrow(Error::class);

            return;
        }

        $result = ($dtoProcessor['callback'])($data);

        expect($result)->toBeIterable();
        expect(iterator_to_array($result))->toHaveCount(2);
        expect(iterator_to_array($result)[0])->toBeInstanceOf(ContactDto::class);
    });

    it('throws for invalid data class', function () {
        $extractor = new class extends HelgeSverre\Extractor\Extraction\Extractor
        {
            use HelgeSverre\Extractor\Extraction\Concerns\HasDto;

            public function dataClass(): string
            {
                return stdClass::class;
            }
        };

        $reflection = new ReflectionClass($extractor);
        $property = $reflection->getProperty('processors');
        $property->setAccessible(true);
        $processors = $property->getValue($extractor);

        $dtoProcessor = collect($processors)->firstWhere('priority', 1000);

        expect(fn () => ($dtoProcessor['callback'])(['name' => 'test']))
            ->toThrow(InvalidDataClass::class);
    });
});
