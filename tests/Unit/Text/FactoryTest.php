<?php

use HelgeSverre\Extractor\Contracts\TextLoader;
use HelgeSverre\Extractor\Facades\Text;
use HelgeSverre\Extractor\Text\Factory;
use HelgeSverre\Extractor\Text\Loaders\Html;
use HelgeSverre\Extractor\Text\Loaders\Pdf;
use HelgeSverre\Extractor\Text\Loaders\Rtf;
use HelgeSverre\Extractor\Text\Loaders\Text as PlainTextLoader;
use HelgeSverre\Extractor\Text\Loaders\Web;
use HelgeSverre\Extractor\Text\Loaders\Word;

describe('Text Factory', function () {
    describe('create method', function () {
        it('creates HTML loader', function () {
            $factory = app(Factory::class);
            $loader = $factory->create('html');

            expect($loader)->toBeInstanceOf(Html::class);
            expect($loader)->toBeInstanceOf(TextLoader::class);
        });

        it('creates PDF loader', function () {
            $factory = app(Factory::class);
            $loader = $factory->create('pdf');

            expect($loader)->toBeInstanceOf(Pdf::class);
        });

        it('creates text loader', function () {
            $factory = app(Factory::class);
            $loader = $factory->create('text');

            expect($loader)->toBeInstanceOf(PlainTextLoader::class);
        });

        it('creates RTF loader', function () {
            $factory = app(Factory::class);
            $loader = $factory->create('rtf');

            expect($loader)->toBeInstanceOf(Rtf::class);
        });

        it('creates web loader', function () {
            $factory = app(Factory::class);
            $loader = $factory->create('web');

            expect($loader)->toBeInstanceOf(Web::class);
        });

        it('creates word loader', function () {
            $factory = app(Factory::class);
            $loader = $factory->create('word');

            expect($loader)->toBeInstanceOf(Word::class);
        });

        it('throws exception for invalid type', function () {
            $factory = app(Factory::class);
            $factory->create('invalid-type');
        })->throws(InvalidArgumentException::class, 'Invalid text loader type');
    });

    describe('fromMime method', function () {
        it('returns null for blank content', function () {
            $factory = app(Factory::class);

            expect($factory->fromMime('text/plain', ''))->toBeNull();
            expect($factory->fromMime('text/plain', null))->toBeNull();
        });

        it('uses HTML loader for html mime types', function () {
            $factory = app(Factory::class);
            $result = $factory->fromMime('text/html', '<html><body>Hello</body></html>');

            expect($result)->not->toBeNull();
            expect($result->toString())->toContain('Hello');
        });

        it('uses HTML loader for xml mime types', function () {
            $factory = app(Factory::class);
            $result = $factory->fromMime('application/xml', '<root><data>Test</data></root>');

            expect($result)->not->toBeNull();
        });

        it('uses text loader for text/plain', function () {
            $factory = app(Factory::class);
            $result = $factory->fromMime('text/plain', 'Plain text content');

            expect($result)->not->toBeNull();
            expect($result->toString())->toContain('Plain text content');
        });

        it('uses RTF loader for text/rtf', function () {
            $factory = app(Factory::class);
            $rtfContent = file_get_contents(__DIR__.'/../../samples/contract.rtf');
            $result = $factory->fromMime('text/rtf', $rtfContent);

            expect($result)->not->toBeNull();
        });
    });

    describe('convenience methods', function () {
        it('has html convenience method', function () {
            $result = Text::html('<html><body>Test</body></html>');

            expect($result)->not->toBeNull();
            expect($result->toString())->toContain('Test');
        });

        it('has text convenience method', function () {
            $result = Text::text('  Trimmed   text   ');

            expect($result)->not->toBeNull();
        });

        it('has pdf convenience method', function () {
            $pdfContent = file_get_contents(__DIR__.'/../../samples/laravel-certification-invoice.pdf');
            $result = Text::pdf($pdfContent);

            expect($result)->not->toBeNull();
            expect($result->toString())->toContain('Laravel');
        });

        it('has word convenience method', function () {
            $docContent = file_get_contents(__DIR__.'/../../samples/contract.docx');
            $result = Text::word($docContent);

            expect($result)->not->toBeNull();
        });

        it('has rtf convenience method', function () {
            $rtfContent = file_get_contents(__DIR__.'/../../samples/contract.rtf');
            $result = Text::rtf($rtfContent);

            expect($result)->not->toBeNull();
        });
    });

    describe('Macroable trait', function () {
        it('allows extending with macros', function () {
            Factory::macro('customMethod', function () {
                return 'custom result';
            });

            $factory = app(Factory::class);

            expect($factory->customMethod())->toBe('custom result');
        });
    });
});
