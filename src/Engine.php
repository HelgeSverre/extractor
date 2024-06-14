<?php

namespace HelgeSverre\Extractor;

use HelgeSverre\Extractor\Extraction\Extractor;
use HelgeSverre\Extractor\Text\ImageContent;
use HelgeSverre\Extractor\Text\TextContent;
use InvalidArgumentException;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse as ChatResponse;
use OpenAI\Responses\Completions\CreateResponse as CompletionResponse;

class Engine
{
    // New
    const GPT_4_1106_PREVIEW = 'gpt-4-1106-preview';

    const GPT_4_VISION = 'gpt-4-vision-preview';

    const GPT_3_TURBO_1106 = 'gpt-3.5-turbo-1106';

    // GPT-4
    const GPT_4 = 'gpt-4';

    const GPT4_32K = 'gpt-4-32k';

    const GPT_4o = 'gpt-4o';

    // GPT-3.5
    const GPT_3_TURBO_INSTRUCT = 'gpt-3.5-turbo-instruct';

    const GPT_3_TURBO_16K = 'gpt-3.5-turbo-16k';

    const GPT_3_TURBO = 'gpt-3.5-turbo';

    // Legacy
    const TEXT_DAVINCI_003 = 'text-davinci-003';

    const TEXT_DAVINCI_002 = 'text-davinci-002';

    public function run(
        Extractor $extractor,
        TextContent|string $input,
        string $model,
        int $maxTokens,
        float $temperature,
    ): mixed {
        $preprocessed = $extractor->preprocess($input);

        $prompt = $extractor->prompt($preprocessed);

        $response = match (true) {
            // Legacy text completion models
            $this->isCompletionModel($model) => OpenAI::completions()->create([
                'model' => $model,
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'prompt' => $prompt,
            ]),

            // New json mode models.
            $this->isVisionModel($model) => OpenAI::chat()->create([
                'model' => $model,
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => $prompt,
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => match (true) {
                                        $input instanceof ImageContent && $input->isUrl() => $input->content(),
                                        $input instanceof ImageContent && $input->isBase64able() => $input->toBase64Url(),
                                        default => throw new InvalidArgumentException('TODO: replace this exception message')
                                    },
                                ],
                            ],
                        ],
                    ],
                ],
            ]),

            $this->isJsonModeCompatibleModel($model) => OpenAI::chat()->create([
                'model' => $model,
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'response_format' => ['type' => 'json_object'],
                'messages' => [[
                    'role' => 'user',
                    'content' => $prompt,
                ]],
            ]),

            // Previous generation models
            default => OpenAI::chat()->create([
                'model' => $model,
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'messages' => [[
                    'role' => 'user',
                    'content' => $prompt,
                ]],
            ]),
        };

        $text = $this->extractResponseText($response);

        return $extractor->process($text);
    }

    public function isVisionModel(string $model): bool
    {
        return in_array($model, [
            self::GPT_4_VISION,
            self::GPT_4o,
        ]);
    }

    public function isCompletionModel(string $model): bool
    {
        return in_array($model, [
            self::GPT_3_TURBO_INSTRUCT,
            self::TEXT_DAVINCI_003,
            self::TEXT_DAVINCI_002,
        ]);
    }

    public function isJsonModeCompatibleModel(string $model): bool
    {
        return in_array($model, [
            self::GPT_4_1106_PREVIEW,
            self::GPT_3_TURBO_1106,
        ]);
    }

    public function extractResponseText(ChatResponse|CompletionResponse $response): mixed
    {
        return $response instanceof ChatResponse
            ? $response->choices[0]->message->content
            : $response->choices[0]->text;
    }
}
