<?php

namespace HelgeSverre\Extractor\Engines;

use HelgeSverre\Extractor\Contracts\Engine;
use HelgeSverre\Extractor\Extraction\Extractor;
use HelgeSverre\Extractor\Text\TextContent;
use Illuminate\Support\Facades\Http;

class OllamaEngine implements Engine
{
    public function run(
        Extractor $extractor,
        TextContent|string $input,
        string $model,
        int $maxTokens,
        float $temperature,
    ): mixed {
        $preprocessed = $extractor->preprocess($input);

        $prompt = $extractor->prompt($preprocessed);

        dump($prompt);

        $response = Http::timeout(60)->post('http://localhost:11434/api/generate', [
            'prompt' => $prompt,
            'model' => $model,
            'stream' => false,
            'format' => 'json',
            'options' => [
                'temperature' => $temperature,
                // 'maxTokens' => $maxTokens,
            ],
        ]);

        dump($response->body());

        $text = $response->json('response');

        return $extractor->process($text);
    }
}
