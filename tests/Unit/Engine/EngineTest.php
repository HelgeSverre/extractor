<?php

use HelgeSverre\Extractor\Engine;
use HelgeSverre\Extractor\Extraction\Extractor;
use HelgeSverre\Extractor\Text\ImageContent;
use OpenAI\Responses\Chat\CreateResponse as ChatResponse;
use OpenAI\Responses\Meta\MetaInformation;

describe('Engine', function () {
    describe('model constants', function () {
        describe('GPT-5 family', function () {
            it('has correct GPT_5_2 value', function () {
                expect(Engine::GPT_5_2)->toBe('gpt-5.2');
            });

            it('has correct GPT_5_1 value', function () {
                expect(Engine::GPT_5_1)->toBe('gpt-5.1');
            });

            it('has correct GPT_5 value', function () {
                expect(Engine::GPT_5)->toBe('gpt-5');
            });

            it('has correct GPT_5_MINI value', function () {
                expect(Engine::GPT_5_MINI)->toBe('gpt-5-mini');
            });
        });

        describe('GPT-4.1 family', function () {
            it('has correct GPT_4_1 value', function () {
                expect(Engine::GPT_4_1)->toBe('gpt-4.1');
            });

            it('has correct GPT_4_1_MINI value', function () {
                expect(Engine::GPT_4_1_MINI)->toBe('gpt-4.1-mini');
            });

            it('has correct GPT_4_1_NANO value', function () {
                expect(Engine::GPT_4_1_NANO)->toBe('gpt-4.1-nano');
            });
        });

        describe('GPT-4o family', function () {
            it('has correct GPT_4O value', function () {
                expect(Engine::GPT_4O)->toBe('gpt-4o');
            });

            it('has correct GPT_4O_MINI value', function () {
                expect(Engine::GPT_4O_MINI)->toBe('gpt-4o-mini');
            });

            it('has correct GPT_4_TURBO value', function () {
                expect(Engine::GPT_4_TURBO)->toBe('gpt-4-turbo');
            });
        });

        describe('O-series reasoning models', function () {
            it('has correct O3 value', function () {
                expect(Engine::O3)->toBe('o3');
            });

            it('has correct O3_MINI value', function () {
                expect(Engine::O3_MINI)->toBe('o3-mini');
            });

            it('has correct O3_PRO value', function () {
                expect(Engine::O3_PRO)->toBe('o3-pro');
            });

            it('has correct O4_MINI value', function () {
                expect(Engine::O4_MINI)->toBe('o4-mini');
            });
        });

        describe('deprecated aliases', function () {
            it('has GPT_4_OMNI as alias for GPT_4O', function () {
                expect(Engine::GPT_4_OMNI)->toBe('gpt-4o');
                expect(Engine::GPT_4_OMNI)->toBe(Engine::GPT_4O);
            });

            it('has GPT_4_OMNI_MINI as alias for GPT_4O_MINI', function () {
                expect(Engine::GPT_4_OMNI_MINI)->toBe('gpt-4o-mini');
                expect(Engine::GPT_4_OMNI_MINI)->toBe(Engine::GPT_4O_MINI);
            });

            it('has GPT_4o as alias for GPT_4O', function () {
                expect(Engine::GPT_4o)->toBe(Engine::GPT_4O);
            });
        });
    });

    describe('buildMessages', function () {
        it('builds system and user messages for text input', function () {
            $engine = new Engine;
            $extractor = Mockery::mock(Extractor::class);
            $extractor->shouldReceive('systemPrompt')->andReturn('You are a helpful assistant.');

            $method = new ReflectionMethod($engine, 'buildMessages');

            $messages = $method->invoke($engine, $extractor, 'some text input', 'Extract the data');

            expect($messages)->toHaveCount(2);
            expect($messages[0])->toBe([
                'role' => 'system',
                'content' => 'You are a helpful assistant.',
            ]);
            expect($messages[1])->toBe([
                'role' => 'user',
                'content' => 'Extract the data',
            ]);
        });

        it('omits system message when systemPrompt is null', function () {
            $engine = new Engine;
            $extractor = Mockery::mock(Extractor::class);
            $extractor->shouldReceive('systemPrompt')->andReturn(null);

            $method = new ReflectionMethod($engine, 'buildMessages');

            $messages = $method->invoke($engine, $extractor, 'some text input', 'Extract the data');

            expect($messages)->toHaveCount(1);
            expect($messages[0]['role'])->toBe('user');
            expect($messages[0]['content'])->toBe('Extract the data');
        });

        it('omits system message when systemPrompt is empty string', function () {
            $engine = new Engine;
            $extractor = Mockery::mock(Extractor::class);
            $extractor->shouldReceive('systemPrompt')->andReturn('');

            $method = new ReflectionMethod($engine, 'buildMessages');

            $messages = $method->invoke($engine, $extractor, 'some text input', 'Extract the data');

            expect($messages)->toHaveCount(1);
            expect($messages[0]['role'])->toBe('user');
        });

        it('builds multipart user content for ImageContent with URL', function () {
            $engine = new Engine;
            $extractor = Mockery::mock(Extractor::class);
            $extractor->shouldReceive('systemPrompt')->andReturn('You are a helpful assistant.');

            $imageContent = ImageContent::url('https://example.com/image.png');

            $method = new ReflectionMethod($engine, 'buildMessages');

            $messages = $method->invoke($engine, $extractor, $imageContent, 'Describe the image');

            expect($messages)->toHaveCount(2);
            expect($messages[0]['role'])->toBe('system');
            expect($messages[1]['role'])->toBe('user');
            expect($messages[1]['content'])->toBeArray();
            expect($messages[1]['content'])->toHaveCount(2);
            expect($messages[1]['content'][0])->toBe([
                'type' => 'text',
                'text' => 'Describe the image',
            ]);
            expect($messages[1]['content'][1]['type'])->toBe('image_url');
            expect($messages[1]['content'][1]['image_url']['url'])->toBe('https://example.com/image.png');
        });
    });

    describe('extractResponseText', function () {
        it('extracts text from a ChatResponse', function () {
            $engine = new Engine;

            $meta = MetaInformation::from([
                'openai-model' => ['gpt-4o'],
                'openai-organization' => ['org-test'],
                'openai-version' => ['2024-01-01'],
                'openai-processing-ms' => ['100'],
                'x-request-id' => ['req-test'],
            ]);

            $response = ChatResponse::from([
                'id' => 'chatcmpl-test',
                'object' => 'chat.completion',
                'created' => 1234567890,
                'model' => 'gpt-4o',
                'choices' => [
                    [
                        'index' => 0,
                        'message' => [
                            'role' => 'assistant',
                            'content' => '{"name": "John Doe"}',
                        ],
                        'finish_reason' => 'stop',
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 10,
                    'completion_tokens' => 20,
                    'total_tokens' => 30,
                ],
            ], $meta);

            expect($engine->extractResponseText($response))->toBe('{"name": "John Doe"}');
        });
    });
});
