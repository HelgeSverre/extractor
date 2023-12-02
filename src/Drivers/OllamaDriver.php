<?php

namespace HelgeSverre\Extractor\Drivers;

use HelgeSverre\Extractor\Contracts\Engine;
use HelgeSverre\Extractor\Extraction\Extractor;
use HelgeSverre\Extractor\Text\TextContent;
use Illuminate\Support\Facades\Http;

class OllamaDriver implements Engine
{
    // TODO: put in config
    protected string $baseUrl = 'http://localhost:11434';

    protected int $timeout = 300;

    public function run(
        Extractor          $extractor,
        TextContent|string $input,
        array              $config = [],
        ?string            $model = null,
    ): mixed
    {
        $preprocessed = $extractor->preprocess($input);

        $prompt = $extractor->prompt($preprocessed);

        $response = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->post('/api/generate', array_filter([
                'model' => $model,
                'prompt' => $prompt,
                'format' => 'json',
                'stream' => false,
                'options' => $config,
            ]));

        $responseData = $response->json();

        dump($responseData);

        $text = $responseData['response'] ?? '';

        return $extractor->process($text);
    }
}
