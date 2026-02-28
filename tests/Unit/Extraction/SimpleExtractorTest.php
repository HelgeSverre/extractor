<?php

use HelgeSverre\Extractor\Extraction\Builtins\Simple;

describe('Simple Extractor', function () {
    it('throws when no view is configured', function () {
        $extractor = new Simple;

        $extractor->viewName();
    })->throws(InvalidArgumentException::class, 'No view provided');

    it('returns configured view name', function () {
        $extractor = new Simple(['view' => 'extractor::custom']);

        expect($extractor->viewName())->toBe('extractor::custom');
    });
});
