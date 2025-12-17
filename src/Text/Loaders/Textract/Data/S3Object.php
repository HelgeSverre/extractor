<?php

declare(strict_types=1);

namespace HelgeSverre\Extractor\Text\Loaders\Textract\Data;

class S3Object
{
    public function __construct(
        public readonly string $bucket,
        public readonly string $name,
        public readonly ?string $version = null
    ) {}

    public function getClientRequestToken(): string
    {
        return md5($this->name.$this->bucket.$this->version);
    }
}
