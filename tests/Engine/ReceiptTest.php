<?php

use HelgeSverre\Extractor\Engine;
use HelgeSverre\Extractor\Extraction\Builtins\Receipt;
use HelgeSverre\Extractor\Facades\Extractor;
use HelgeSverre\Extractor\Facades\Text;

it('can extract contact list from text sample with gpt4 json mode', function () {

    $sample = Text::pdf(file_get_contents(__DIR__.'/../samples/electronics.pdf'));

    $data = Extractor::extract(Receipt::class, $sample, [
        'model' => Engine::GPT4_1106_PREVIEW,
    ]);

    dump($data);
});

it('can extract contact list from text sample with gpt 3.5 json mode', function () {
    $sample = Text::pdf(file_get_contents(__DIR__.'/../samples/electronics.pdf'));

    $data = Extractor::extract(Receipt::class, $sample, [
        'model' => Engine::GPT_3_TURBO_1106,
    ]);

    dump($data->toArray());
});
