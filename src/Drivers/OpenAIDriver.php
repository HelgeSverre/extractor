<?php

namespace HelgeSverre\Extractor\Drivers;

use HelgeSverre\Extractor\Contracts\Engine;
use HelgeSverre\Extractor\Extraction\Extractor;
use HelgeSverre\Extractor\Text\TextContent;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse as ChatResponse;
use OpenAI\Responses\Completions\CreateResponse as CompletionResponse;

class OpenAIDriver implements Engine
{
    // New
    const GPT_4_1106_PREVIEW = 'gpt-4-1106-preview';

    const GPT_3_TURBO_1106 = 'gpt-3.5-turbo-1106';

    // GPT-4
    const GPT_4 = 'gpt-4';

    const GPT4_32K = 'gpt-4-32k';

    // GPT-3.5
    const GPT_3_TURBO_INSTRUCT = 'gpt-3.5-turbo-instruct';

    const GPT_3_TURBO_16K = 'gpt-3.5-turbo-16k';

    const GPT_3_TURBO = 'gpt-3.5-turbo';

    // Legacy
    const TEXT_DAVINCI_003 = 'text-davinci-003';

    const TEXT_DAVINCI_002 = 'text-davinci-002';

    public function run(
        Extractor          $extractor,
        TextContent|string $input,
        array              $config = [],
        ?string            $model = null,
    ): mixed
    {
        $preprocessed = $extractor->preprocess($input);

        $prompt = $extractor->prompt($preprocessed);

        $response = match (true) {
            // Legacy text completion models
            $this->isCompletionModel($model) => OpenAI::completions()->create([
                'model' => $model,
                'max_tokens' => $config["max_tokens"] ?? null,
                'temperature' => $config["temperature"] ?? null,
                'prompt' => $prompt,
            ]),

            // New json mode models.
            $this->supportsJsonMode($model) => OpenAI::chat()->create([
                'model' => $model,
                'max_tokens' => $config["max_tokens"] ?? null,
                'temperature' => $config["temperature"] ?? null,
                'response_format' => ['type' => 'json_object'],
                'messages' => [[
                    'role' => 'user',
                    'content' => $prompt,
                ]],
            ]),

            // Previous generation models
            default => OpenAI::chat()->create([
                'model' => $model,
                'max_tokens' => $config["max_tokens"] ?? null,
                'temperature' => $config["temperature"] ?? null,
                'messages' => [[
                    'role' => 'user',
                    'content' => $prompt,
                ]],
            ]),
        };

        $text = $this->extractResponseText($response);

        return $extractor->process($text);
    }

    public function isCompletionModel(string $model): bool
    {
        return in_array($model, [
            'gpt-3.5-turbo-instruct',
            'text-davinci-003',
            'text-davinci-002',
        ]);
    }

    public function supportsJsonMode(string $model): bool
    {
        return in_array($model, [
            'gpt-4-1106-preview',
            'gpt-3.5-turbo-1106',
        ]);
    }

    public function extractResponseText(ChatResponse|CompletionResponse $response): mixed
    {
        return $response instanceof ChatResponse
            ? $response->choices[0]->message->content
            : $response->choices[0]->text;
    }
}
