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

    expect($data)->toBeArray()
        // First offer: NORA RØDKÅL
        ->and($data[0]['offer_name'])->toBe('NORA RØDKÅL')
        ->and($data[0]['offer_text'])->toBe('-30%')
        ->and($data[0]['subtext'])->toBe('450 g, pr. kg')
        ->and($data[0]['offer_type'])->toBe('percentage_off')
        ->and($data[0]['price'])->toBeNull()
        ->and($data[0]['price_per_weight_unit'])->toBe('30.96')
        ->and((int) $data[0]['weight'])->toBe(450)
        ->and($data[0]['weight_unit'])->toBe('g')

        // Second offer: NORA SURKÅL
        ->and($data[1]['offer_name'])->toBe('NORA SURKÅL')
        ->and($data[1]['offer_text'])->toBe('-30%')
        ->and($data[1]['subtext'])->toBe('450 g, pr. kg')
        ->and($data[1]['offer_type'])->toBe('percentage_off')
        ->and($data[1]['price'])->toBeNull()
        ->and($data[1]['price_per_weight_unit'])->toBe('30.96')
        ->and((int) $data[1]['weight'])->toBe(450)
        ->and($data[1]['weight_unit'])->toBe('g')

        // Third offer: PÆRER
        ->and($data[2]['offer_name'])->toBe('PÆRER')
        ->and($data[2]['offer_text'])->toBe('20')
        ->and($data[2]['subtext'])->toBe('Pr. kg Belgia/Holland')
        ->and($data[2]['offer_type'])->toBe('discounted_price')
        ->and((float) $data[2]['price'])->toBe(20)
        ->and($data[2]['price_per_weight_unit'])->toBeNull()
        ->and($data[2]['weight'])->toBeNull()
        ->and($data[2]['weight_unit'])->toBe('kg');
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

    expect($data)->toBeArray()
        // First offer: Fjordland Middag
        ->and($data[0]['offer_name'])->toBe('Fjordland Middag')
        ->and($data[0]['offer_type'])->toBe('percentage_off')
        ->and($data[0]['price'])->toBeNull()
        ->and((int) $data[0]['weight'])->toBe(350)
        ->and($data[0]['weight_unit'])->toBe('g')

        // Second offer: Coop Kyllingfilet
        ->and($data[1]['offer_name'])->toBe('Coop Kyllingfilet')
        ->and($data[1]['offer_type'])->toBe('discounted_price')
        ->and((float) $data[1]['price'])->toBe(89.90)
        ->and((int) $data[1]['weight'])->toBe(690)
        ->and($data[1]['weight_unit'])->toBe('g')

        // Third offer: Synnøve Gulost Original
        ->and($data[2]['offer_name'])->toBe('Synnøve Gulost Original')
        ->and($data[2]['offer_type'])->toBe('discounted_price')
        ->and((float) $data[2]['price'])->toBe(89.90)
        ->and($data[2]['weight'])->toBeNull()
        ->and($data[2]['weight_unit'])->toBeNull()

        // Fourth offer: Utvalgte Coca-Cola/Mineralvann
        ->and($data[3]['offer_name'])->toBe('Utvalgte Coca-Cola/Mineralvann')
        ->and($data[3]['offer_type'])->toBe('multi_buy_discount')
        ->and($data[3]['price'])->toBeNull()
        ->and((float) $data[3]['weight'])->toBe(1.5)
        ->and($data[3]['weight_unit'])->toBe('L')

        // Fifth offer: Freia Plater
        ->and($data[4]['offer_name'])->toBe('Freia Plater')
        ->and($data[4]['offer_type'])->toBe('percentage_off')
        ->and($data[4]['price'])->toBeNull()
        ->and((int) $data[4]['weight'])->toBe(150)
        ->and($data[4]['weight_unit'])->toBe('g');
});
