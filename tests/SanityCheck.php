<?php

use HelgeSverre\Extractor\Enums\Model;
use HelgeSverre\Extractor\Facades\Extractor;
use HelgeSverre\Extractor\Facades\Text;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Completions\CreateResponse as CompletionResponse;

it('can extract a complex rundown', function () {

    $text = Text::pdf(file_get_contents(__DIR__.'/../tests/samples/rundown.pdf'));

    $data = Extractor::extract('rundown', $text);

    dump($data);

});

it('can extract contacts from a website', function () {

    $text = Text::web('https://crescat.io/contact/');

    $data = Extractor::extract('contacts', $text);

    dump($data);

});

it('validates returning parsed receipt as array', function () {
    OpenAI::fake([
        CompletionResponse::fake([
            'model' => 'gpt-3.5-turbo',
            'choices' => [['text' => file_get_contents(__DIR__.'/samples/wolt-pizza-norwegian.json')]],
        ]),
    ]);

    $text = file_get_contents(__DIR__.'/samples/wolt-pizza-norwegian.txt');
    $result = Extractor::extract('receipt', $text, model: Model::TURBO_INSTRUCT);

    expect($result)->toBeArray()
        ->and($result['totalAmount'])->toBe(568.00)
        ->and($result['orderRef'])->toBe('61e4fb2646c424c5cbc9bc88')
        ->and($result['date'])->toBe('2023-07-21')
        ->and($result['taxAmount'])->toBe(74.08)
        ->and($result['currency'])->toBe('NOK')
        ->and($result['merchant']['name'])->toBe('Minde Pizzeria')
        ->and($result['merchant']['vatId'])->toBe('921670362MVA')
        ->and($result['merchant']['address'])->toBe('Conrad Mohrs veg 5, 5068 Bergen, NOR');
});
