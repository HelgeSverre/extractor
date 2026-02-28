<?php

use HelgeSverre\Extractor\Text\TextContent;
use Illuminate\Support\Stringable;

describe('TextContent', function () {
    it('stores content via constructor', function () {
        $text = new TextContent('hello world');

        expect($text->toString())->toBe('hello world');
    });

    it('creates instance via static make()', function () {
        $text = TextContent::make('test content');

        expect($text)->toBeInstanceOf(TextContent::class);
        expect($text->toString())->toBe('test content');
    });

    it('returns normalized whitespace', function () {
        $text = new TextContent("  hello   world  \n\n  foo  ");

        $normalized = $text->normalized();

        expect($normalized)->toBe('hello world foo');
    });

    it('returns a Laravel Stringable', function () {
        $text = new TextContent('hello world');

        $stringable = $text->asStringable();

        expect($stringable)->toBeInstanceOf(Stringable::class);
        expect($stringable->upper()->toString())->toBe('HELLO WORLD');
    });

    it('implements __toString', function () {
        $text = new TextContent('cast me');

        expect((string) $text)->toBe('cast me');
    });

    it('works with string concatenation', function () {
        $text = new TextContent('hello');

        expect('say: '.$text)->toBe('say: hello');
    });
});
