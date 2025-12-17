<?php

use HelgeSverre\Extractor\Extraction\Builtins\Fields;
use HelgeSverre\Extractor\Extraction\Builtins\Receipt;

describe('Extractor Base Class', function () {
    describe('configuration', function () {
        it('accepts config array in constructor', function () {
            $extractor = new Fields(['key' => 'value']);

            expect($extractor->config('key'))->toBe('value');
        });

        it('returns null for missing config with no default', function () {
            $extractor = new Fields;

            expect($extractor->config('nonexistent'))->toBeNull();
        });

        it('returns default value for missing config', function () {
            $extractor = new Fields;

            expect($extractor->config('nonexistent', 'default'))->toBe('default');
        });

        it('merges config via mergeConfig', function () {
            $extractor = new Fields(['a' => 1]);
            $extractor->mergeConfig(['b' => 2]);

            expect($extractor->config('a'))->toBe(1);
            expect($extractor->config('b'))->toBe(2);
        });

        it('adds single config via addConfig', function () {
            $extractor = new Fields;
            $extractor->addConfig('key', 'value');

            expect($extractor->config('key'))->toBe('value');
        });
    });

    describe('model/maxTokens/temperature config', function () {
        it('returns null model when not configured', function () {
            $extractor = new Fields;

            expect($extractor->model())->toBeNull();
        });

        it('returns configured model', function () {
            $extractor = new Fields(['model' => 'gpt-4o']);

            expect($extractor->model())->toBe('gpt-4o');
        });

        it('returns null maxTokens when not configured', function () {
            $extractor = new Fields;

            expect($extractor->maxTokens())->toBeNull();
        });

        it('returns configured maxTokens', function () {
            $extractor = new Fields(['max_tokens' => 4000]);

            expect($extractor->maxTokens())->toBe(4000);
        });

        it('returns null temperature when not configured', function () {
            $extractor = new Fields;

            expect($extractor->temperature())->toBeNull();
        });

        it('returns configured temperature', function () {
            $extractor = new Fields(['temperature' => 0.5]);

            expect($extractor->temperature())->toBe(0.5);
        });
    });

    describe('preprocessors', function () {
        it('registers preprocessor with default priority', function () {
            $extractor = new Fields;
            $extractor->registerPreprocessor(fn ($input) => $input.' modified');

            $result = $extractor->preprocess('test');

            expect($result)->toBe('test modified');
        });

        it('runs preprocessors in priority order', function () {
            $extractor = new Fields;
            $extractor->registerPreprocessor(fn ($input) => $input.'-second', 200);
            $extractor->registerPreprocessor(fn ($input) => $input.'-first', 100);

            $result = $extractor->preprocess('start');

            expect($result)->toBe('start-first-second');
        });

        it('passes extractor instance to preprocessor', function () {
            $extractor = new Fields;
            $receivedExtractor = null;

            $extractor->registerPreprocessor(function ($input, $ext) use (&$receivedExtractor) {
                $receivedExtractor = $ext;

                return $input;
            });

            $extractor->preprocess('test');

            expect($receivedExtractor)->toBe($extractor);
        });
    });

    describe('processors', function () {
        it('runs processors in priority order', function () {
            $extractor = new Fields;

            // Clear default processors for this test
            $reflection = new ReflectionClass($extractor);
            $property = $reflection->getProperty('processors');
            $property->setAccessible(true);
            $property->setValue($extractor, []);

            $extractor->registerProcessor(fn ($input) => $input.'-second', 200);
            $extractor->registerProcessor(fn ($input) => $input.'-first', 100);

            $result = $extractor->process('start');

            expect($result)->toBe('start-first-second');
        });
    });

    describe('naming', function () {
        it('generates slug name from class name', function () {
            $extractor = new Fields;

            expect($extractor->name())->toBe('fields');
        });

        it('generates view name from class name', function () {
            $extractor = new Fields;

            expect($extractor->viewName())->toBe('extractor::fields');
        });

        it('generates different names for different extractors', function () {
            $fields = new Fields;
            $receipt = new Receipt;

            expect($fields->name())->not->toBe($receipt->name());
        });
    });
});
