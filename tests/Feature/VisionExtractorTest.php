<?php

use HelgeSverre\Extractor\Engine;
use HelgeSverre\Extractor\Facades\Extractor;
use HelgeSverre\Extractor\Text\ImageContent;

it('Can extract REMA offer catalog details data from an image', function () {
    $data = Extractor::fields(
        ImageContent::raw(file_get_contents(__DIR__.'/../samples/rema.png')),
        fields: [
            'offer_name',
            'price',
            'weight',
            'weight_unit',
        ],
        model: Engine::GPT_4_OMNI,
        maxTokens: 500,
    );

    expect($data)->toBeArray()
        ->and($data[0]['offer_name'])->toContain('KJØTTDEIG')
        ->and($data[0]['weight_unit'])->toBe('g');
})->skip('Flaky: AI model output varies between runs');

it('Can extract BUNNPRIS offer catalog details data from an image', function () {
    $data = Extractor::fields(
        ImageContent::file(__DIR__.'/../samples/bunnpris-offer-catalog.webp', 'image/webp'),
        fields: [
            'offer_name' => 'product name',
            'offer_text' => 'specific text for the offer',
            'offer_type' => 'discounted_price, percentage_off, multi_buy_discount or other',
            'price',
            'weight',
            'weight_unit',
        ],
        model: Engine::GPT_4_OMNI,
    );

    expect($data)->toBeArray()
        ->and($data[0]['offer_name'])->toBeString();
})->skip('Flaky: AI model output varies between runs');

it('Can extract offer catalog from image url', function () {
    $data = Extractor::fields(
        ImageContent::url('https://image-transformer-api.tjek.com/?u=s3://sgn-prd-assets/uploads/tT1Qp8SZ/p-1.webp&w=1100&s=9c578461839907b4e6fb7dc10d3846bc'),
        fields: [
            'offer_name' => 'product name',
            'offer_type' => 'discounted_price, percentage_off, multi_buy_discount or other',
            'price',
            'weight',
            'weight_unit',
        ],
        model: Engine::GPT_4_OMNI,
    );

    expect($data)->toBeArray()
        ->and($data[0]['offer_name'])->toBeString();
})->skip('Flaky: AI model output varies and external URL may timeout');
