<?php

namespace HelgeSverre\Extractor;

use HelgeSverre\Extractor\Enums\Model;
use HelgeSverre\Extractor\Extraction\Extractor;
use HelgeSverre\Extractor\Text\TextContent;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse as ChatResponse;
use OpenAI\Responses\Completions\CreateResponse as CompletionResponse;

class Engine
{
    const DEFAULT_MODEL = 'gpt-3.5-turbo-16k';

    public function __construct(protected Extractor $extractor)
    {
    }

    public function run(
        TextContent|string $input,
        Model|string $model = null,
        int $maxTokens = null,
        float $temperature = null,
    ): mixed {
        $preprocessed = $this->extractor->preprocess($input);

        $selectedModel = $model?->value ?? $model ?? $this->extractor->model() ?? self::DEFAULT_MODEL;

        $params = [
            'model' => $selectedModel,
            'max_tokens' => $maxTokens ?? $this->extractor->maxTokens() ?? 2000, // These should be a
            'temperature' => $temperature ?? $this->extractor->temperature() ?? 0.1, // These should be a
        ];

        $response = $this->isCompletionModel($selectedModel)
            ? OpenAI::completions()->create([
                ...$params,
                'prompt' => $this->extractor->prompt($preprocessed),
            ])
            : OpenAI::chat()->create([
                ...$params,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $this->extractor->prompt($preprocessed),
                    ],
                ],
            ]);

        $response = $this->extractor->processResponse($response);

        $text = $this->extractResponseText($response);

        return $this->extractor->process($text);
    }

    public function isCompletionModel(string $model): bool
    {
        return in_array($model, [
            'gpt-3.5-turbo-instruct',
            'text-davinci-003',
            'text-davinci-002',
        ]);
    }

    protected function extractResponseText(ChatResponse|CompletionResponse $response): string
    {
        return $response instanceof ChatResponse
            ? $response->choices[0]->message->content
            : $response->choices[0]->text;
    }
}
