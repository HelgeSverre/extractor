<?php

namespace HelgeSverre\Extractor\Extractors\Concerns;

use HelgeSverre\Extractor\Exceptions\InvalidJsonReturnedError;

trait ExtractsJson
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
