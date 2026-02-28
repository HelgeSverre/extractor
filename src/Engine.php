<?php

declare(strict_types=1);

namespace HelgeSverre\Extractor;

use HelgeSverre\Extractor\Extraction\Extractor;
use HelgeSverre\Extractor\Text\ImageContent;
use HelgeSverre\Extractor\Text\TextContent;
use InvalidArgumentException;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse as ChatResponse;

class Engine
{
    // GPT-5 family
    const GPT_5_2 = 'gpt-5.2';

    const GPT_5_1 = 'gpt-5.1';

    const GPT_5 = 'gpt-5';

    const GPT_5_MINI = 'gpt-5-mini';

    // GPT-4.1 family
    const GPT_4_1 = 'gpt-4.1';

    const GPT_4_1_MINI = 'gpt-4.1-mini';

    const GPT_4_1_NANO = 'gpt-4.1-nano';

    // GPT-4o family
    const GPT_4O = 'gpt-4o';

    const GPT_4O_MINI = 'gpt-4o-mini';

    const GPT_4_TURBO = 'gpt-4-turbo';

    // O-series (reasoning models)
    const O3 = 'o3';

    const O3_MINI = 'o3-mini';

    const O3_PRO = 'o3-pro';

    const O4_MINI = 'o4-mini';

    // Deprecated aliases (kept for one release cycle, will be removed in v0.6.0)

    /** @deprecated Use GPT_4O instead. Will be removed in v0.6.0. */
    const GPT_4_OMNI = 'gpt-4o';

    /** @deprecated Use GPT_4O_MINI instead. Will be removed in v0.6.0. */
    const GPT_4_OMNI_MINI = 'gpt-4o-mini';

    /** @deprecated Use GPT_4O instead. Will be removed in v0.6.0. */
    const GPT_4o = self::GPT_4O;

    public function run(
        Extractor $extractor,
        TextContent|string $input,
        string $model,
        int $maxTokens,
        float $temperature,
    ): mixed {
        $preprocessed = $extractor->preprocess($input);

        $prompt = $extractor->prompt($preprocessed);

        $messages = $this->buildMessages($extractor, $input, $prompt);

        $payload = [
            'model' => $model,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
            'messages' => $messages,
            'response_format' => ['type' => 'json_object'],
        ];

        $response = OpenAI::chat()->create($payload);

        $text = $this->extractResponseText($response);

        return $extractor->process($text);
    }

    protected function buildMessages(Extractor $extractor, TextContent|string $input, string $prompt): array
    {
        $messages = [];

        $systemPrompt = $extractor->systemPrompt();
        if ($systemPrompt !== null && $systemPrompt !== '') {
            $messages[] = [
                'role' => 'system',
                'content' => $systemPrompt,
            ];
        }

        if ($input instanceof ImageContent) {
            $messages[] = [
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
                                $input->isUrl() => $input->content(),
                                $input->isBase64able() => $input->toBase64Url(),
                                default => throw new InvalidArgumentException(
                                    'Invalid input type for vision model. Expected ImageContent with URL or base64-encodable content, got: ImageContent('.$input->type().')'
                                )
                            },
                        ],
                    ],
                ],
            ];
        } else {
            $messages[] = [
                'role' => 'user',
                'content' => $prompt,
            ];
        }

        return $messages;
    }

    public function extractResponseText(ChatResponse $response): string
    {
        return $response->choices[0]->message->content;
    }
}
