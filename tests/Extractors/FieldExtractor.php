<?php

use HelgeSverre\Extractor\Engine;
use HelgeSverre\Extractor\Facades\Extractor;
use HelgeSverre\Extractor\Facades\Text;

it('can extract simple fields using gpt 3.5 json mode', function () {
    $sample = Text::html(file_get_contents(__DIR__.'/../samples/event-page.html'));

    $data = Extractor::fields($sample,
        fields: [
            'minimumAge',
            'date',
            'eventName',
            'description',
            'tags',
        ],
        model: Engine::GPT_4_1106_PREVIEW,
    );

    expect($data)->toBeArray()
        ->and($data['minimumAge'])->toBe(20)
        ->and($data['date'])->toBe('2023-12-01')
        ->and(strtolower($data['eventName']))->toBe('oslo deathfest')
        ->and($data['description'])->toBeString()
        ->and($data['tags'])->toBeArray();
});

it('can extract fields with descriptions using gpt 3.5 json mode', function () {
    $sample = Text::html(file_get_contents(__DIR__.'/../samples/event-page.html'));

    $data = Extractor::fields($sample,
        fields: [
            'minimumAge' => '',
            'date' => 'the date of the event in Y-m-d format',
            'doorsOpenAt' => 'The time when the doors open, format in 24 hour time format',
            'eventName' => 'the name of the event',
            'endsAt' => 'When the event should be finished, in Y-m-d H:i:s format',
        ],
        model: Engine::GPT_3_TURBO_1106,
    );

    expect($data)->toBeArray()
        ->and($data['minimumAge'])->toBe(20)
        ->and($data['date'])->toBe('2023-12-01')
        ->and($data['doorsOpenAt'])->toBe('17:00')
        ->and(strtolower($data['eventName']))->toBe('oslo deathfest')
        ->and($data['endsAt'])->toBe('2023-12-01 02:00:00');
});

it('can extract work history from a PDF CV using gpt 3.5 json mode', function () {
    $sample = Text::pdf(file_get_contents(__DIR__.'/../samples/helge-cv.pdf'));

    $data = Extractor::fields($sample,
        fields: [
            'name' => 'the name of the candidate',
            'email',
            'certifications' => 'list of certifications, if any',
            'workHistory' => [
                'companyName',
                'from' => 'Y-m-d if available, Year only if not, null if missing',
                'to' => 'Y-m-d if available, Year only if not, null if missing',
                'text',
            ],
        ],
        model: Engine::GPT_3_TURBO_1106,
    );

    expect($data)->toBeArray()
        ->and($data['name'])->toContain('Helge Sverre')
        ->and($data['email'])->toBe('helge.sverre@gmail.com')
        ->and($data['certifications'])->toMatchArray([
            'Laravel Certified Developer',
            'AWS Certified Developer - Associate',
            'Microsoft Specialist: Programming in C#',
            'MCPS: Microsoft Certified Professional',
            'Zend Certified PHP Engineer',
        ])
        ->and($data['workHistory'])->toHaveCount(7);
});

it('can scrape car data from finn.no car listing with field extraction using gpt 3.5 json mode', function () {
    $sample = Text::html(file_get_contents(__DIR__.'/../samples/car-classifed.html'));

    $data = Extractor::fields($sample,
        fields: [
            'carMake',
            'carModel',
            'milage' => 'in kilometers as a number',
            'modelYear',
            'description',
            'sellerName',
            'sellerPhone',
            'sellerAddress',
            'finnCode',
        ],
        model: Engine::GPT_3_TURBO_1106,
    );

    expect($data)->toBeArray()
        ->and($data['carMake'])->toBe('Skoda')
        ->and($data['carModel'])->toBe('Octavia')
        ->and($data['milage'])->toBe(209000)
        ->and($data['sellerName'])->toBe('HAAVELMOEN BRUKTBILSALG')
        ->and($data['sellerPhone'])->toBe('41692829')
        ->and($data['sellerAddress'])->toBe('Hengsrudveien, 3178 Våle')
        ->and($data['finnCode'])->toBe('331004985');
});
