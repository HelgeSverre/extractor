<?php

use HelgeSverre\Extractor\Engine;
use HelgeSverre\Extractor\Facades\Extractor;
use HelgeSverre\Extractor\Text\ImageContent;

it('Can extract REMA offer catalog details data from an image (converted from WEBP to PNG and ran through TinyPNG) ', function () {
    $data = Extractor::fields(
        ImageContent::raw(file_get_contents(__DIR__.'/../samples/rema.png')),
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
        ->and((float) $data[0]['price'])->toBe(59.9)
        ->and((int) $data[0]['weight'])->toBe(400)
        ->and($data[0]['weight_unit'])->toBe('g')
        // Second
        ->and($data[1]['offer_name'])->toBe('RÅ KALDPRESSET JUICE')
        ->and((float) $data[1]['price'])->toBe(39.9)
        ->and((int) $data[1]['weight'])->toBe(1)
        ->and($data[1]['weight_unit'])->toBe('l');
});

it('Can extract BUNNPRIS offer catalog details data from an image', function () {

    $data = Extractor::fields(
        ImageContent::file(__DIR__.'/../samples/bunnpris-offer-catalog.webp', 'image/webp'),
        fields: [
            'offer_name' => 'product name',
            'offer_text' => 'specific text for the offer',
            'subtext' => 'other text related to the specific offer',
            'offer_type' => 'discounted_price, percentage_off, multi_buy_discount or other',
            'price',
            'price_per_weight_unit' => 'price per kilo/liter or whatever unit, leave blank if not applicable',
            'weight',
            'weight_unit',
        ],
        model: Engine::GPT_4_VISION,
    );
    dd($data);

    expect($data)->toBeArray()
        // First
        ->and($data[0]['offer_name'])->toBe('KJØTTDEIG AV STORFE 14%')
        ->and((float) $data[0]['price'])->toBe(59.9)
        ->and((int) $data[0]['weight'])->toBe(400)
        ->and($data[0]['weight_unit'])->toBe('g')
        // Second
        ->and($data[1]['offer_name'])->toBe('RÅ KALDPRESSET JUICE')
        ->and((float) $data[1]['price'])->toBe(39.9)
        ->and((int) $data[1]['weight'])->toBe(1)
        ->and($data[1]['weight_unit'])->toBe('l');
});

it('Can extract BUNNPRIS offer catalog from image url (tjek)', function () {

    $data = Extractor::fields(
        ImageContent::url('https://image-transformer-api.tjek.com/?u=s3://sgn-prd-assets/uploads/tT1Qp8SZ/p-1.webp&w=1100&s=9c578461839907b4e6fb7dc10d3846bc'),
        fields: [
            'offer_name' => 'product name',
            'offer_type' => 'discounted_price, percentage_off, multi_buy_discount or other',
            'price',
            'weight',
            'weight_unit',
        ],
        model: Engine::GPT_4_VISION,
    );
    dump($data);

    expect($data)->toBeArray();
});
