<?php

use HelgeSverre\Extractor\Facades\Text;
use HelgeSverre\Extractor\Text\Loaders\Textract\TextractService;
use HelgeSverre\Extractor\Text\Loaders\Textract\TextractUsingS3Upload;
use HelgeSverre\Extractor\Text\TextContent;
use Illuminate\Support\Facades\Storage;

it('Can load Text', function () {
    $text = Text::text(file_get_contents(__DIR__.'/../samples/wolt-pizza-norwegian.txt'));

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'Helge Sverre Hessevik Liseth',
        'Conrad Mohrs veg 5',
        'Wolt',
    );
});

it('Can load PDFs', function () {
    $text = Text::pdf(file_get_contents(__DIR__.'/../samples/laravel-certification-invoice.pdf'));

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'contact@laravelcert.com',
        'Helge Sverre Hessevik Liseth',
    );
});

it('Can OCR images', function () {
    $text = Text::textract(file_get_contents(__DIR__.'/../samples/grocery-receipt-norwegian-spar.jpg'));

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'TOMATER',
        'HAKKEDE',
        'SKINKE',
        'SPAR DALE',
    );
})->skip('Takes too long');

it('Can OCR Pdfs', function () {
    $text = Text::textract(file_get_contents(__DIR__.'/../samples/laravel-certification-invoice.pdf'));

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'contact@laravelcert.com',
        'Helge Sverre Hessevik Liseth',
    );
})->skip('Takes too long');

it('Can OCR Pdfs via s3', function () {
    $text = Text::textractUsingS3Upload(file_get_contents(__DIR__.'/../samples/laravel-certification-invoice.pdf'));

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'contact@laravelcert.com',
        'Helge Sverre Hessevik Liseth',
    );
})->skip('Takes too long');

it('Can load a Word documents i found on the internet', function () {
    $text = Text::word(file_get_contents(__DIR__.'/../samples/word-document.doc'));

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'Mauris',
    );
});

it('Can load a Word document exported from google docs', function () {
    $text = Text::word(file_get_contents(__DIR__.'/../samples/contract.docx'));

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'Contract Agreement',
        'Termination of the Agreement',
    );
});

it('Can load a Word document exported from google docs with improved .doc extraction', function () {
    $text = Text::word(file_get_contents(__DIR__.'/../samples/word-file-export-google-docs.docx'));

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'Sample Markdown',
        'Second Heading',
        'The end',
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
    $text = Text::html(file_get_contents(__DIR__.'/../samples/paddle-fake-subscription.html'));

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'Thank you for your purchase!',
        'NOK 1,246.25',
    );
});

it('Can load rtf files', function () {
    $text = Text::rtf(file_get_contents(__DIR__.'/../samples/contract.rtf'));

    expect($text)->toBeInstanceOf(TextContent::class)->and($text->toString())->toContain(
        'Contract Agreement',
        'Termination of the Agreement',
    );
});

it('removes the file from S3 using the provided cleanup callback', function () {
    Storage::fake('s3');

    $mock = Mockery::mock(TextractService::class);
    $mock->shouldReceive('s3ObjectToText');

    $textractUsingS3Upload = new TextractUsingS3Upload($mock);

    $testFilePath = 'extractor/test-file.pdf';

    Storage::disk('s3')->put($testFilePath, 'Test content');

    Storage::disk('s3')->assertExists($testFilePath);

    TextractUsingS3Upload::cleanupFileUsing(function ($path) {
        Storage::disk('s3')->delete($path);
    });

    $textractUsingS3Upload->cleanup($testFilePath);

    Storage::disk('s3')->assertMissing($testFilePath);
});

it('overrides the default file path generation with a custom callback', function () {
    Storage::fake('s3');

    $mock = Mockery::mock(TextractService::class);
    $mock->shouldReceive('s3ObjectToText');

    $textractUsingS3Upload = new TextractUsingS3Upload($mock);

    $customFilePath = 'custom-path/custom-file.pdf';

    TextractUsingS3Upload::generateFilePathUsing(function () use ($customFilePath) {
        return $customFilePath;
    });

    expect($textractUsingS3Upload->getFilePath())->toBe($customFilePath);

    Storage::disk('s3')->put($textractUsingS3Upload->getFilePath(), 'Test content');

    Storage::disk('s3')->assertExists($customFilePath);
});
