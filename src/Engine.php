<?php

namespace HelgeSverre\Extractor;

use HelgeSverre\Extractor\Contracts\Extractor;
use HelgeSverre\Extractor\Enums\Model;
use HelgeSverre\Extractor\Exceptions\InvalidJsonReturnedError;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse as ChatResponse;
use OpenAI\Responses\Completions\CreateResponse as CompletionResponse;

class Engine
{
    // TODO: inject openai client into constructor
    public function __construct(protected Extractor $extractor)
    {
    }

    public function raw(
        string $prompt,
        Model $model = Model::TURBO_16K,
        int $maxTokens = 2000,
        float $temperature = 0.1,
    ): array {
        $response = $this->sendRequest(
            prompt: $prompt,
            params: [
                'model' => $model->value,
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
            ],
            model: $model
        );

        return $this->parseResponse($response);
    }

    public function run(
        TextContent|string $input,
        ?Model $model,
        int $maxTokens = null,
        float $temperature = null,
    ): ?array {
        $preprocessed = $this->extractor->preprocess($input);

        // TODO: Cleanup
        $wip = $model?->value ?? $this->extractor->model() ?? Model::TURBO_INSTRUCT->value;

        $response = $this->sendRequest(
            prompt: $this->extractor->prompt($preprocessed),
            params: [
                'model' => $wip, // These should be a
                'max_tokens' => $maxTokens ?? $this->extractor->maxTokens() ?? 2000, // These should be a
                'temperature' => $temperature ?? $this->extractor->temperature() ?? 0.1, // These should be a
            ],
            model: Model::TURBO_INSTRUCT
        );

        $text = $this->extractResponseText($response);

        return $this->extractor->process($text);
    }

    protected function sendRequest(string $prompt, array $params, Model $model): ChatResponse|CompletionResponse
    {
        return $model->isCompletion()
            ? OpenAI::completions()->create(array_merge($params, ['prompt' => $prompt]))
            : OpenAI::chat()->create(array_merge($params, ['messages' => [['role' => 'user', 'content' => $prompt]]]));
    }

    protected function parseResponse(ChatResponse|CompletionResponse $response): array
    {
        $json = $this->extractResponseText($response);

        $decoded = json_decode($json, true);

        if ($decoded === null) {
            throw new InvalidJsonReturnedError("Invalid JSON returned:\n$json");
        }

        return $decoded;
    }

    protected function extractResponseText(ChatResponse|CompletionResponse $response): string
    {
        return $response instanceof ChatResponse
            ? $response->choices[0]->message->content
            : $response->choices[0]->text;
    }
}
