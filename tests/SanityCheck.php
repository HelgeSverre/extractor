<?php

use HelgeSverre\Extractor\Engine;
use HelgeSverre\Extractor\Extraction\Builtins\Contacts;
use HelgeSverre\Extractor\Facades\Extractor;
use HelgeSverre\Extractor\Facades\Text;

it('can extract contacts from a website with a class-string extractor', function () {
    \OpenAI\Laravel\Facades\OpenAI::fake();
    $text = Text::web('https://crescat.io/contact/');
    /** @var \HelgeSverre\Extractor\ContactDto $data */
    $data = Extractor::extract(Contacts::class, $text, model: Engine::GPT_4_1106_PREVIEW);
    dump($data->toArray());
});

it('can extract contacts from a website an extractor instance ', function () {
    \OpenAI\Laravel\Facades\OpenAI::fake();
    $text = Text::web('https://crescat.io/contact/');
    $data = Extractor::extract(new Contacts, $text, model: Engine::GPT_3_TURBO_INSTRUCT);
    dump($data);
});

it('can extract contacts from a website with custom model and alias of extractor', function () {
    \OpenAI\Laravel\Facades\OpenAI::fake();
    $text = Text::web('https://crescat.io/contact/');
    Extractor::extend('dummy', fn () => new Contacts);
    $data = Extractor::extract('dummy', $text, model: Engine::GPT_3_TURBO_INSTRUCT);
    dump($data);
});
