<?php

use HelgeSverre\Extractor\Engine;

describe('Engine', function () {
    describe('model detection', function () {
        describe('isCompletionModel', function () {
            it('returns true for GPT_3_TURBO_INSTRUCT', function () {
                $engine = new Engine;
                expect($engine->isCompletionModel(Engine::GPT_3_TURBO_INSTRUCT))->toBeTrue();
            });

            it('returns true for TEXT_DAVINCI_003', function () {
                $engine = new Engine;
                expect($engine->isCompletionModel(Engine::TEXT_DAVINCI_003))->toBeTrue();
            });

            it('returns true for TEXT_DAVINCI_002', function () {
                $engine = new Engine;
                expect($engine->isCompletionModel(Engine::TEXT_DAVINCI_002))->toBeTrue();
            });

            it('returns false for chat models', function () {
                $engine = new Engine;
                expect($engine->isCompletionModel(Engine::GPT_4_OMNI))->toBeFalse();
                expect($engine->isCompletionModel(Engine::GPT_4_TURBO))->toBeFalse();
                expect($engine->isCompletionModel(Engine::GPT_3_TURBO))->toBeFalse();
            });
        });

        describe('isJsonModeCompatibleModel', function () {
            it('returns true for GPT_4_1106_PREVIEW', function () {
                $engine = new Engine;
                expect($engine->isJsonModeCompatibleModel(Engine::GPT_4_1106_PREVIEW))->toBeTrue();
            });

            it('returns true for GPT_3_TURBO_1106', function () {
                $engine = new Engine;
                expect($engine->isJsonModeCompatibleModel(Engine::GPT_3_TURBO_1106))->toBeTrue();
            });

            it('returns true for GPT_4_OMNI', function () {
                $engine = new Engine;
                expect($engine->isJsonModeCompatibleModel(Engine::GPT_4_OMNI))->toBeTrue();
            });

            it('returns true for GPT_4_OMNI_MINI', function () {
                $engine = new Engine;
                expect($engine->isJsonModeCompatibleModel(Engine::GPT_4_OMNI_MINI))->toBeTrue();
            });

            it('returns false for older models', function () {
                $engine = new Engine;
                expect($engine->isJsonModeCompatibleModel(Engine::GPT_4))->toBeFalse();
                expect($engine->isJsonModeCompatibleModel(Engine::GPT_3_TURBO))->toBeFalse();
            });
        });

        describe('isVisionModel', function () {
            it('returns true for GPT_4_VISION', function () {
                $engine = new Engine;
                expect($engine->isVisionModel(Engine::GPT_4_VISION))->toBeTrue();
            });

            it('returns true for GPT_4_OMNI', function () {
                $engine = new Engine;
                expect($engine->isVisionModel(Engine::GPT_4_OMNI))->toBeTrue();
            });

            it('returns false for text-only models', function () {
                $engine = new Engine;
                expect($engine->isVisionModel(Engine::GPT_4_TURBO))->toBeFalse();
                expect($engine->isVisionModel(Engine::GPT_3_TURBO))->toBeFalse();
            });
        });

        describe('isHybridModel', function () {
            it('returns true for GPT_4o alias', function () {
                $engine = new Engine;
                expect($engine->isHybridModel(Engine::GPT_4o))->toBeTrue();
            });

            it('returns false for other models', function () {
                $engine = new Engine;
                expect($engine->isHybridModel(Engine::GPT_4_TURBO))->toBeFalse();
                expect($engine->isHybridModel(Engine::GPT_3_TURBO))->toBeFalse();
            });
        });

        describe('isOhOne', function () {
            it('returns true for GPT_O1_MINI', function () {
                $engine = new Engine;
                expect($engine->isOhOne(Engine::GPT_O1_MINI))->toBeTrue();
            });

            it('returns true for GPT_O1_PREVIEW', function () {
                $engine = new Engine;
                expect($engine->isOhOne(Engine::GPT_O1_PREVIEW))->toBeTrue();
            });

            it('returns false for non-o1 models', function () {
                $engine = new Engine;
                expect($engine->isOhOne(Engine::GPT_4_OMNI))->toBeFalse();
            });
        });
    });

    describe('model constants', function () {
        it('has correct GPT_4_OMNI value', function () {
            expect(Engine::GPT_4_OMNI)->toBe('gpt-4o');
        });

        it('has correct GPT_4_OMNI_MINI value', function () {
            expect(Engine::GPT_4_OMNI_MINI)->toBe('gpt-4o-mini');
        });

        it('has GPT_4o as alias to GPT_4_OMNI', function () {
            expect(Engine::GPT_4o)->toBe(Engine::GPT_4_OMNI);
        });

        it('has correct GPT_4_TURBO value', function () {
            expect(Engine::GPT_4_TURBO)->toBe('gpt-4-turbo');
        });

        it('has correct GPT_3_TURBO_1106 value', function () {
            expect(Engine::GPT_3_TURBO_1106)->toBe('gpt-3.5-turbo-1106');
        });
    });
});
