<?php

use HelgeSverre\Extractor\Engine;
use HelgeSverre\Extractor\Extraction\Builtins\Receipt;
use HelgeSverre\Extractor\Facades\Extractor;
use HelgeSverre\Extractor\Facades\Text;

it('can extract receipt from pdf using gpt4 json mode', function () {

    $sample = Text::pdf(file_get_contents(__DIR__.'/../samples/electronics.pdf'));

    $data = Extractor::extract(Receipt::class, $sample, [
        'model' => Engine::GPT_4_1106_PREVIEW,
    ]);

    expect($data)->toBeArray()
        ->and($data['orderRef'])->toBe('2e1463f119ad4785b8d68a3b7ecd0195-2229703009')
        ->and($data['date'])->toBe('2023-11-30')
        ->and($data['taxAmount'])->toBe(179.8)
        ->and($data['totalAmount'])->toBe(899.0)
        ->and($data['currency'])->toBe('NOK')
        ->and($data['merchant'])->toBeArray()
        ->and($data['merchant']['name'])->toBe('Elkjøp Bergen Xhibition')
        ->and($data['merchant']['vatId'])->toBe('NO947054600MVA')
        ->and($data['merchant']['address'])->toBe('Småstrandgaten 3, 5014 Bergen')
        ->and($data['lineItems'])->toBeArray()->and($data['lineItems'])->toHaveCount(1)
        ->and($data['lineItems'][0])->toBeArray()
        ->and($data['lineItems'][0]['text'])->toBe('PlayStation 5 - PS5 DualSense trådløs kontroller (hvit)')
        ->and($data['lineItems'][0]['qty'])->toBe(1)
        ->and($data['lineItems'][0]['price'])->toBe(899.0)
        ->and($data['lineItems'][0]['sku'])->toBe('220282');

});

it('can extract receipt from pdf using gpt 3.5 json mode', function () {
    $sample = Text::pdf(file_get_contents(__DIR__.'/../samples/electronics.pdf'));

    $data = Extractor::extract(Receipt::class, $sample, [
        'model' => Engine::GPT_3_TURBO_1106,
    ]);

    expect($data)->toBeArray()
        ->and($data['date'])->toBe('2023-11-30')
        ->and($data['taxAmount'])->toBe(179.8)
        ->and($data['totalAmount'])->toBe(899.0)
        ->and($data['currency'])->toBe('NOK')
        ->and($data['merchant'])->toBeArray()
        ->and($data['merchant']['name'])->toBe('Elkjøp Bergen Xhibition')
        ->and($data['lineItems'])->toBeArray()->and($data['lineItems'])->toHaveCount(1)
        ->and($data['lineItems'][0])->toBeArray()
        ->and($data['lineItems'][0]['text'])->toBe('PlayStation 5 - PS5 DualSense trådløs kontroller (hvit)')
        ->and($data['lineItems'][0]['qty'])->toBe(1)
        ->and($data['lineItems'][0]['price'])->toBe(899.0)
        ->and((string) $data['lineItems'][0]['sku'])->toBe('220282');

});
