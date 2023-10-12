<?php

use HelgeSverre\Extractor\Facades\Text;
use HelgeSverre\Extractor\TextContent;

it('Can load Text', function () {
    $text = Text::text(file_get_contents(__DIR__.'/samples/wolt-pizza-norwegian.txt'));

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'Helge Sverre Hessevik Liseth',
        'Conrad Mohrs veg 5',
        'Wolt',
    );
});

it('Can load PDFs', function () {
    $text = Text::pdf(file_get_contents(__DIR__.'/samples/laravel-certification-invoice.pdf'));

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'contact@laravelcert.com',
        'Helge Sverre Hessevik Liseth',
    );
});

it('Can OCR images', function () {
    $text = Text::textract(file_get_contents(__DIR__.'/samples/grocery-receipt-norwegian-spar.jpg'));

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'TOMATER',
        'HAKKEDE',
        'SKINKE',
        'SPAR DALE',
    );
});

it('Can OCR Pdfs', function () {
    $text = Text::textractUsingS3Upload(file_get_contents(__DIR__.'/samples/laravel-certification-invoice.pdf'));

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'contact@laravelcert.com',
        'Helge Sverre Hessevik Liseth',
    );
});

it('Can load a Word documents i found on the internet', function () {
    $text = Text::word(file_get_contents(__DIR__.'/samples/word-document.doc'));

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'Mauris',
    );
});

it('Can load a Word document exported from google docs', function () {
    $text = Text::word(file_get_contents(__DIR__.'/samples/contract.docx'));

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'Contract Agreement',
        'Termination of the Agreement',
    );
});

it('Can load text from website', function () {
    $text = Text::web('https://sparksuite.github.io/simple-html-invoice-template/');

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'Sparksuite',
        'Total: $385.00',
    );
});

it('Can load html files', function () {
    $text = Text::html(file_get_contents(__DIR__.'/samples/paddle-fake-subscription.html'));

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'Thank you for your purchase!',
        'NOK 1,246.25',
    );
});

it('Can load rtf files', function () {
    $text = Text::rtf(file_get_contents(__DIR__.'/samples/contract.rtf'));

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'Contract Agreement',
        'Termination of the Agreement',
    );
});
