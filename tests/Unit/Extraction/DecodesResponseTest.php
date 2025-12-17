<?php

use HelgeSverre\Extractor\Exceptions\InvalidJsonReturnedError;
use HelgeSverre\Extractor\Extraction\Builtins\Fields;

describe('DecodesResponse Trait', function () {
    describe('extractJsonString', function () {
        it('returns valid JSON as-is', function () {
            $extractor = new Fields;
            $json = '{"name": "John", "age": 30}';

            $result = $extractor->extractJsonString($json);

            expect($result)->toBe($json);
        });

        it('extracts JSON from markdown code blocks', function () {
            $extractor = new Fields;
            $response = "Here is the data:\n```json\n{\"name\": \"John\"}\n```";

            $result = $extractor->extractJsonString($response);

            expect($result)->toBe('{"name": "John"}');
        });

        it('handles response with text before and after JSON block', function () {
            $extractor = new Fields;
            $response = "I found the following:\n```json\n{\"key\": \"value\"}\n```\nHope this helps!";

            $result = $extractor->extractJsonString($response);

            expect($result)->toBe('{"key": "value"}');
        });

        it('returns original response if no valid JSON found', function () {
            $extractor = new Fields;
            $response = 'This is not JSON at all';

            $result = $extractor->extractJsonString($response);

            expect($result)->toBe($response);
        });
    });

    describe('expectedOutputKey', function () {
        it('returns output as default key', function () {
            $extractor = new Fields;

            expect($extractor->expectedOutputKey())->toBe('output');
        });
    });

    describe('throwsOnInvalidJsonResponse', function () {
        it('returns true by default', function () {
            $extractor = new Fields;

            expect($extractor->throwsOnInvalidJsonResponse())->toBeTrue();
        });
    });

    describe('JSON processing', function () {
        it('processes valid JSON response', function () {
            $extractor = new Fields;

            // Clear processors except the JSON decoder
            $reflection = new ReflectionClass($extractor);
            $property = $reflection->getProperty('processors');
            $property->setAccessible(true);
            $processors = $property->getValue($extractor);

            // Find the DecodesResponse processor (priority -1000)
            $jsonProcessor = null;
            foreach ($processors as $p) {
                if ($p['priority'] === -1000) {
                    $jsonProcessor = $p['callback'];
                    break;
                }
            }

            expect($jsonProcessor)->not->toBeNull();

            // Test it processes valid JSON
            $result = $jsonProcessor('{"output": {"name": "John"}}', $extractor);

            expect($result)->toBe(['name' => 'John']);
        });

        it('throws on invalid JSON when configured', function () {
            $extractor = new Fields;

            // Get the processor
            $reflection = new ReflectionClass($extractor);
            $property = $reflection->getProperty('processors');
            $property->setAccessible(true);
            $processors = $property->getValue($extractor);

            $jsonProcessor = null;
            foreach ($processors as $p) {
                if ($p['priority'] === -1000) {
                    $jsonProcessor = $p['callback'];
                    break;
                }
            }

            expect(fn () => $jsonProcessor('not valid json {', $extractor))
                ->toThrow(InvalidJsonReturnedError::class);
        });

        it('returns full decoded array when output key not present', function () {
            $extractor = new Fields;

            $reflection = new ReflectionClass($extractor);
            $property = $reflection->getProperty('processors');
            $property->setAccessible(true);
            $processors = $property->getValue($extractor);

            $jsonProcessor = null;
            foreach ($processors as $p) {
                if ($p['priority'] === -1000) {
                    $jsonProcessor = $p['callback'];
                    break;
                }
            }

            $result = $jsonProcessor('{"different_key": {"name": "John"}}', $extractor);

            expect($result)->toBe(['different_key' => ['name' => 'John']]);
        });
    });
});
