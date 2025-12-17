<?php

declare(strict_types=1);

namespace HelgeSverre\Extractor;

use Spatie\LaravelData\Data;

class ContactDto extends Data
{
    public function __construct(
        public string $name,
        public string $title,
        public string $phone,
        public string $email,
    ) {}
}
