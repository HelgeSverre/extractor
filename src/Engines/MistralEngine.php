<?php

namespace HelgeSverre\Extractor\Engines;

use HelgeSverre\Extractor\Contracts\Engine;
use HelgeSverre\Extractor\Extraction\Extractor;
use HelgeSverre\Extractor\Text\TextContent;
use HelgeSverre\Mistral\Mistral;

class MistralEngine implements Engine
{
    // New
    const tiny = 'mistral-tiny';

    const small = 'mistral-small';

    const medium = 'mistral-medium';

    protected Mistral $mistral;

    public function __construct($apiKey)
    {
        $this->mistral = new Mistral($apiKey);

    }

    public function run(
        Extractor $extractor,
        TextContent|string $input,
        string $model,
        int $maxTokens,
        float $temperature,
    ): mixed {
        $preprocessed = $extractor->preprocess($input);

        $prompt = $extractor->prompt($preprocessed);

        dd($prompt
        );
        $response = $this->mistral->simpleChat()->create([
            [
                'role' => 'user',
                'content' => $prompt,
            ],
        ],
            model: $model,
            temperature: $temperature,
            maxTokens: $maxTokens,
        );

        $text = $response->content;

        return $extractor->process($text);
    }
}
