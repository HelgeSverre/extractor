<?php

namespace HelgeSverre\Extractor\Services\Textract\Data;

class S3Object
{
    public function __construct(
        readonly public string $bucket,
        readonly public string $name,
        readonly public ?string $version = null
    ) {
    }

    public function getClientRequestToken(): string
    {
        return md5($this->name.$this->bucket.$this->version);
    }
}
