<?php

use HelgeSverre\Extractor\Engine;
use HelgeSverre\Extractor\Facades\Extractor;
use HelgeSverre\Extractor\Text\ImageContent;

it('Can extract REMA offer catalog details data from an image (converted from WEBP to PNG and ran through TinyPNG) ', function () {
    $data = Extractor::fields(
        ImageContent::raw(file_get_contents(__DIR__ . '/../samples/rema.png')),
        fields: [
            'offer_name',
            'price',
            'weight',
            'weight_unit',
        ],
        model: Engine::GPT_4_VISION,
        maxTokens: 500,
    );

    expect($data)->toBeArray()
        // First
        ->and($data[0]['offer_name'])->toBe('KJØTTDEIG AV STORFE 14%')
        ->and((float)$data[0]['price'])->toBe(59.9)
        ->and((int)$data[0]['weight'])->toBe(400)
        ->and($data[0]['weight_unit'])->toBe('g')
        // Second
        ->and($data[1]['offer_name'])->toBe('RÅ KALDPRESSET JUICE')
        ->and((float)$data[1]['price'])->toBe(39.9)
        ->and((int)$data[1]['weight'])->toBe(1)
        ->and($data[1]['weight_unit'])->toBe('l');
});

it('Can extract BUNNPRIS offer catalog details data from an image', function () {


    $data = Extractor::fields(
        ImageContent::file(__DIR__ . '/../samples/bunnpris-offer-catalog.webp', "image/webp"),
        fields: [
            'offer_name',
            'price',
            'weight',
            'weight_unit',
        ],
        model: Engine::GPT_4_VISION,
        maxTokens: 500,
    );
    dd($data);

    expect($data)->toBeArray()
        // First
        ->and($data[0]['offer_name'])->toBe('KJØTTDEIG AV STORFE 14%')
        ->and((float)$data[0]['price'])->toBe(59.9)
        ->and((int)$data[0]['weight'])->toBe(400)
        ->and($data[0]['weight_unit'])->toBe('g')
        // Second
        ->and($data[1]['offer_name'])->toBe('RÅ KALDPRESSET JUICE')
        ->and((float)$data[1]['price'])->toBe(39.9)
        ->and((int)$data[1]['weight'])->toBe(1)
        ->and($data[1]['weight_unit'])->toBe('l');
});
