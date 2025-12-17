<?php

declare(strict_types=1);

namespace HelgeSverre\Extractor\Extraction\Concerns;

use HelgeSverre\Extractor\Exceptions\InvalidJsonReturnedError;
use HelgeSverre\Extractor\Extraction\Extractor;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @mixin Extractor
 */
trait DecodesResponse
{
    public function throwsOnInvalidJsonResponse(): bool
    {
        return true;
    }

    public function expectedOutputKey(): string
    {
        return 'output';
    }

    public function extractJsonString($response): ?string
    {
        // It's already valid JSON
        if (json_validate($response)) {
            return $response;
        }

        // Attempt to extract the JSON from a Markdown code block.
        // TODO: make this case in-sensitive (JSON vs json vs Json)
        $maybeJson = Str::of($response)->between('```json', '```')->trim();

        if ($maybeJson->isJson()) {
            return $maybeJson->toString();
        }

        // TODO: Attempt to recover incorrectly formatted json (missing comma, unclosed brace etc

        // TODO: Idea: optional property you can enable on extractor to attempt to "fix" the broken JSON by calling the openai model again.

        return $response;
    }

    public function bootDecodesResponse(): void
    {
        $this->registerPreprocessor(function ($input): mixed {
            $this->addConfig('outputKey', $this->expectedOutputKey());

            return $input;
        });

        $this->registerProcessor(function ($response): mixed {

            $maybeJson = $this->extractJsonString($response);

            $decoded = json_decode($maybeJson, true);

            if (json_last_error() !== JSON_ERROR_NONE && $this->throwsOnInvalidJsonResponse()) {
                throw new InvalidJsonReturnedError(
                    'Invalid JSON returned: '.json_last_error_msg()."\nResponse:\n$response"
                );
            }

            $key = $this->expectedOutputKey();

            if (Arr::has($decoded, $key)) {
                return Arr::get($decoded, $key);
            }

            return $decoded;

        }, -1000);
    }
}
