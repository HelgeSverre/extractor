<?php

use HelgeSverre\Extractor\Text\Loaders\Word;
use HelgeSverre\Extractor\Text\TextContent;

describe('Word Loader', function () {
    it('rejects non-string input', function () {
        $loader = new Word;
        $loader->load(['array', 'data']);
    })->throws(InvalidArgumentException::class, 'Word loader expects string data');

    it('rejects files exceeding size limit', function () {
        $loader = new Word;
        $hugeData = str_repeat('x', 51 * 1024 * 1024); // 51MB
        $loader->load($hugeData);
    })->throws(InvalidArgumentException::class, 'Document size exceeds maximum');

    it('loads valid docx files', function () {
        $loader = new Word;
        $content = file_get_contents(__DIR__.'/../../samples/contract.docx');
        $result = $loader->load($content);

        expect($result)->toBeInstanceOf(TextContent::class);
        expect($result->toString())->toContain('Contract Agreement');
    });

    it('loads valid doc files', function () {
        $loader = new Word;
        $content = file_get_contents(__DIR__.'/../../samples/word-document.doc');
        $result = $loader->load($content);

        expect($result)->toBeInstanceOf(TextContent::class);
        expect($result->toString())->toContain('Mauris');
    });

    it('handles google docs exported docx files', function () {
        $loader = new Word;
        $content = file_get_contents(__DIR__.'/../../samples/word-file-export-google-docs.docx');
        $result = $loader->load($content);

        expect($result)->toBeInstanceOf(TextContent::class);
        expect($result->toString())->toContain('Sample Markdown');
    });
});
