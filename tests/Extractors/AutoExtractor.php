<?php

use HelgeSverre\Extractor\Engine;
use HelgeSverre\Extractor\Facades\Extractor;
use HelgeSverre\Extractor\Facades\Text;

it('can extract simple fields using gpt 3.5 json mode', function () {
    $sample = Text::html(file_get_contents(__DIR__.'/../samples/event-page.html'));

    $data = Extractor::auto($sample,
        fields: [
            'minimumAge',
            'date',
            'eventName',
            'description',
            'tags',
        ],
        model: Engine::GPT_3_TURBO_1106,
    );

    dump($data);
});
it('can extract fields with descriptions using gpt 3.5 json mode', function () {
    $sample = Text::html(file_get_contents(__DIR__.'/../samples/event-page.html'));

    $data = Extractor::auto($sample,
        fields: [
            'minimumAge' => '',
            'date' => 'the date of the event in Y-m-d format',
            'doorsOpenAt' => 'The time when the doors open, format in 24 hour time format',
            'eventName' => 'the name of the event',
            'endsAt' => 'When the event should be finished, in Y-m-d H:i:s format',
        ],
        model: Engine::GPT_3_TURBO_1106,
    );

    dump($data);
});
