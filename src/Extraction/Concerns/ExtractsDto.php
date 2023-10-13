<?php

namespace HelgeSverre\Extractor\Extraction\Concerns;

use HelgeSverre\Extractor\Exceptions\InvalidJsonReturnedError;
use HelgeSverre\Extractor\Extraction\Extractor;

/**
 * @mixin Extractor
 */
trait ExtractsDto
{
    /**
     * @throws InvalidJsonReturnedError
     */
    public function process(string $response): mixed
    {
        $decoded = json_decode($response, true);

        if ($decoded === null) {
            throw new InvalidJsonReturnedError("Invalid JSON returned:\n$response");
        }

        return $decoded;
    }
}
