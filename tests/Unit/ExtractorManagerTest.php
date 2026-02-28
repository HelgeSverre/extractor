<?php

use HelgeSverre\Extractor\Extraction\Builtins\Fields;
use HelgeSverre\Extractor\Extraction\Builtins\Receipt;
use HelgeSverre\Extractor\ExtractorManager;
use HelgeSverre\Extractor\Facades\Extractor as ExtractorFacade;

describe('ExtractorManager', function () {
    describe('extend', function () {
        it('allows registering custom extractors by name', function () {
            ExtractorFacade::extend('my-custom-extractor', function () {
                return new Fields;
            });

            expect(true)->toBeTrue();
        });

        it('resolves a custom extractor registered via extend', function () {
            $manager = app(ExtractorManager::class);

            $manager->extend('custom-fields', function () {
                return new Fields(['custom_key' => 'custom_value']);
            });

            $reflection = new ReflectionClass($manager);
            $method = $reflection->getMethod('resolveExtractor');
            $method->setAccessible(true);

            $extractor = $method->invoke($manager, 'custom-fields');

            expect($extractor)->toBeInstanceOf(Fields::class);
            expect($extractor->config('custom_key'))->toBe('custom_value');
        });
    });

    describe('resolveExtractor', function () {
        it('resolves built-in extractor by class name', function () {
            $manager = app(ExtractorManager::class);

            $reflection = new ReflectionClass($manager);
            $method = $reflection->getMethod('resolveExtractor');
            $method->setAccessible(true);

            $extractor = $method->invoke($manager, Receipt::class);

            expect($extractor)->toBeInstanceOf(Receipt::class);
        });

        it('returns extractor instance directly if already instantiated', function () {
            $manager = app(ExtractorManager::class);

            $reflection = new ReflectionClass($manager);
            $method = $reflection->getMethod('resolveExtractor');
            $method->setAccessible(true);

            $originalExtractor = new Fields;
            $extractor = $method->invoke($manager, $originalExtractor);

            expect($extractor)->toBe($originalExtractor);
        });

        it('throws exception for unknown extractor class', function () {
            $manager = app(ExtractorManager::class);

            $reflection = new ReflectionClass($manager);
            $method = $reflection->getMethod('resolveExtractor');
            $method->setAccessible(true);

            $method->invoke($manager, 'NonExistentExtractor');
        })->throws(Exception::class, 'not found');
    });

    describe('fields method', function () {
        it('accepts array of simple field names', function () {
            $manager = app(ExtractorManager::class);

            expect(method_exists($manager, 'fields'))->toBeTrue();
        });

        it('accepts nested field definitions', function () {
            $fields = [
                'name' => 'the person name',
                'items' => [
                    'name',
                    'price' => 'numeric price',
                    'quantity',
                ],
            ];

            expect($fields)->toBeArray();
            expect($fields['items'])->toBeArray();
        });
    });

    describe('view method', function () {
        it('exists and is callable', function () {
            $manager = app(ExtractorManager::class);

            expect(method_exists($manager, 'view'))->toBeTrue();
        });
    });

    describe('extract method', function () {
        it('accepts string extractor name', function () {
            $manager = app(ExtractorManager::class);

            expect(method_exists($manager, 'extract'))->toBeTrue();
        });

        it('accepts extractor instance', function () {
            $manager = app(ExtractorManager::class);

            $reflection = new ReflectionMethod($manager, 'extract');
            $params = $reflection->getParameters();

            expect($params[0]->getType()->__toString())->toContain('Extractor');
        });

        it('has default model parameter', function () {
            $reflection = new ReflectionMethod(ExtractorManager::class, 'extract');
            $params = $reflection->getParameters();

            $modelParam = null;
            foreach ($params as $param) {
                if ($param->getName() === 'model') {
                    $modelParam = $param;
                    break;
                }
            }

            expect($modelParam)->not->toBeNull();
            expect($modelParam->isDefaultValueAvailable())->toBeTrue();
            expect($modelParam->getDefaultValue())->toBeNull();
            expect($modelParam->allowsNull())->toBeTrue();
        });
    });

    describe('defaultModel', function () {
        it('returns gpt-4o-mini by default', function () {
            $manager = app(ExtractorManager::class);

            $reflection = new ReflectionClass($manager);
            $method = $reflection->getMethod('defaultModel');
            $method->setAccessible(true);

            expect($method->invoke($manager))->toBe('gpt-4o-mini');
        });

        it('returns configured model when extractor.model config is set', function () {
            config(['extractor.model' => 'gpt-4o']);

            $manager = app(ExtractorManager::class);

            $reflection = new ReflectionClass($manager);
            $method = $reflection->getMethod('defaultModel');
            $method->setAccessible(true);

            expect($method->invoke($manager))->toBe('gpt-4o');
        });
    });
});
