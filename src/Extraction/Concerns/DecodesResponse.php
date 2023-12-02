<?php

namespace HelgeSverre\Extractor\Extraction\Concerns;

use HelgeSverre\Extractor\Exceptions\InvalidJsonReturnedError;
use HelgeSverre\Extractor\Extraction\Extractor;
use Illuminate\Support\Arr;

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
        return 'results';
    }

    public function bootDecodesResponse(): void
    {
        $this->registerProcessor(function ($response): mixed {
            $decoded = json_decode($response, true);

            if ($decoded === null && $this->throwsOnInvalidJsonResponse()) {
                throw new InvalidJsonReturnedError("Invalid JSON returned:\n$response");
            }

            $key = $this->expectedOutputKey();

            if (Arr::has($decoded, $key)) {
                return Arr::get($decoded, $key);
            }

            return $decoded;

        }, -1000);
    }
}
