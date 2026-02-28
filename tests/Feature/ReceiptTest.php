<?php

use HelgeSverre\Extractor\Engine;
use HelgeSverre\Extractor\Extraction\Builtins\Receipt;
use HelgeSverre\Extractor\Facades\Extractor;
use HelgeSverre\Extractor\Facades\Text;

it('can extract receipt from pdf using gpt-4-turbo', function () {

    $sample = Text::pdf(file_get_contents(__DIR__.'/../samples/electronics.pdf'));

    $data = Extractor::extract(Receipt::class, $sample, model: Engine::GPT_4_TURBO);

    expect($data)->toBeArray()
        ->and($data['date'])->toBe('2023-11-30')
        ->and($data['taxAmount'])->toBe(179.8)
        ->and($data['totalAmount'])->toBe(2384.0)
        ->and($data['currency'])->toBe('NOK')
        ->and($data['merchant'])->toBeArray()
        ->and($data['lineItems'])->toBeArray()->and($data['lineItems'])->toHaveCount(1)
        ->and($data['lineItems'][0])->toBeArray()
        ->and($data['lineItems'][0]['qty'])->toBe(1)
        ->and($data['lineItems'][0]['price'])->toBe(899.0);

});

it('can extract receipt from pdf using gpt-4o-mini', function () {

    $sample = Text::pdf(file_get_contents(__DIR__.'/../samples/electronics.pdf'));

    $data = Extractor::extract(Receipt::class, $sample, model: Engine::GPT_4O_MINI);

    expect($data)->toBeArray()
        ->and($data['date'])->toBe('2023-11-30')
        ->and($data['taxAmount'])->toBe(179.8)
        ->and($data['totalAmount'])->toBe(2384.0)
        ->and($data['currency'])->toBe('NOK')
        ->and($data['merchant'])->toBeArray()
        ->and($data['lineItems'])->toBeArray()->and($data['lineItems'])->toHaveCount(1)
        ->and($data['lineItems'][0])->toBeArray()
        ->and($data['lineItems'][0]['qty'])->toBe(1)
        ->and($data['lineItems'][0]['price'])->toBe(899.0);

});

it('can extract receipt from pdf using default model', function () {
    $sample = Text::pdf(file_get_contents(__DIR__.'/../samples/electronics.pdf'));

    $data = Extractor::extract(Receipt::class, $sample);

    expect($data)->toBeArray()
        ->and($data['date'])->toBe('2023-11-30')
        ->and($data['totalAmount'])->toBe(2384.0)
        ->and($data['currency'])->toBe('NOK')
        ->and($data['merchant'])->toBeArray()
        ->and($data['lineItems'])->toBeArray()->and($data['lineItems'])->toHaveCount(1);

});
