<?php

use HelgeSverre\Extractor\Extraction\Builtins\Fields;

describe('System Prompt', function () {
    it('returns config value when set on extractor', function () {
        $extractor = new Fields(['system_prompt' => 'You are a helpful assistant.']);

        expect($extractor->systemPrompt())->toBe('You are a helpful assistant.');
    });

    it('falls back to global config', function () {
        config()->set('extractor.system_prompt', 'Global system prompt');

        $extractor = new Fields;

        expect($extractor->systemPrompt())->toBe('Global system prompt');
    });

    it('returns null when both are null', function () {
        config()->set('extractor.system_prompt', null);

        $extractor = new Fields;

        expect($extractor->systemPrompt())->toBeNull();
    });
});
