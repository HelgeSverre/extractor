<?php

use HelgeSverre\Extractor\Drivers\OpenAIDriver;
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
        model: OpenAIDriver::GPT_4_1106_PREVIEW,
    );

    dump($data);
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
        model: OpenAIDriver::GPT_3_TURBO_1106,
    );

    dump($data);
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
        model: OpenAIDriver::GPT_3_TURBO_1106,
    );

    dump($data);
});

it('can scrape car data from finn.no car listing with field extraction using gpt 3.5 json mode', function () {
    $sample = Text::web('https://www.finn.no/car/used/ad.html?finnkode=331004985');

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
        model: OpenAIDriver::GPT_3_TURBO_1106,
    );

    dump($data);
});
