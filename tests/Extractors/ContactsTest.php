<?php

use HelgeSverre\Extractor\Engine;
use HelgeSverre\Extractor\Extraction\Builtins\Contacts;
use HelgeSverre\Extractor\Facades\Extractor;

it('can extract contact list from text sample with turbo instruct model', function () {
    $sample = file_get_contents(__DIR__.'/../samples/contacts.txt');

    $data = Extractor::extract(Contacts::class, $sample, [
        'model' => Engine::GPT_3_TURBO_INSTRUCT,
    ]);

    dump($data->toArray());
});

it('can extract contact list from text sample with gpt4 json mode', function () {
    $sample = file_get_contents(__DIR__.'/../samples/contacts.txt');
    $data = Extractor::extract(Contacts::class, $sample, [
        'model' => Engine::GPT_4_1106_PREVIEW,
    ]);

    dump($data->toArray());
});

it('can extract contact list from text sample with gpt 3.5 json mode', function () {
    $sample = file_get_contents(__DIR__.'/../samples/contacts.txt');
    $data = Extractor::extract(Contacts::class, $sample, [
        'model' => Engine::GPT_3_TURBO_1106,
    ]);

    dump($data->toArray());
});

it('can extract contact list from text sample with legacy davinci 003 model', function () {
    $sample = file_get_contents(__DIR__.'/../samples/contacts.txt');
    $data = Extractor::extract(Contacts::class, $sample, model: 'text-davinci-003');

    dump($data->toArray());
});
