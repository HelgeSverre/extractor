<?php

namespace HelgeSverre\Extractor\Extraction\Concerns;

use HelgeSverre\Extractor\Exceptions\InvalidJsonReturnedError;
use HelgeSverre\Extractor\Extraction\Extractor;

/**
 * @mixin Extractor
 */
trait ExpectsJson
{
    public function bootExpectsJson()
    {
        $this->registerProcessor(function (string $response) {
            $decoded = json_decode($response, true);

            if ($decoded === null) {
                throw new InvalidJsonReturnedError("Invalid JSON returned:\n$response");
            }

            return $decoded;
        });
    }
}
