<?php

namespace HelgeSverre\Extractor\Extraction\Concerns;

use HelgeSverre\Extractor\Exceptions\InvalidJsonReturnedError;
use HelgeSverre\Extractor\Extraction\Extractor;

/**
 * @mixin Extractor
 */
trait ExpectsJson
{
    /**
     * @throws InvalidJsonReturnedError
     */
    public function handle(string $response): mixed
    {
        $decoded = json_decode($response, true);

        if ($decoded === null) {
            throw new InvalidJsonReturnedError("Invalid JSON returned:\n$response");
        }

        return $decoded;
    }

    public function bootExpectsJson()
    {
        $this->registerProcessor([$this, 'handle']);
    }
}
