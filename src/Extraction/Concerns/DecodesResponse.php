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
        return 'output';
    }

    public function bootDecodesResponse(): void
    {
        $this->registerPreprocessor(function ($input): mixed {
            $this->addConfig('outputKey', $this->expectedOutputKey());

            return $input;
        });

        $this->registerProcessor(function ($response): mixed {

            $decoded = json_decode($response, true);

            if ($decoded === null && $this->throwsOnInvalidJsonResponse()) {
                // TODO: Attempt recovery by looking between first/last { and }
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
