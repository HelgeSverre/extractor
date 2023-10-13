<?php

namespace HelgeSverre\Extractor\Text;

use HelgeSverre\Extractor\Contracts\TextLoader;
use HelgeSverre\Extractor\Text\Loaders\Html;
use HelgeSverre\Extractor\Text\Loaders\Pdf;
use HelgeSverre\Extractor\Text\Loaders\Rtf;
use HelgeSverre\Extractor\Text\Loaders\Text;
use HelgeSverre\Extractor\Text\Loaders\Textract\Textract;
use HelgeSverre\Extractor\Text\Loaders\Textract\TextractUsingS3Upload;
use HelgeSverre\Extractor\Text\Loaders\Web;
use HelgeSverre\Extractor\Text\Loaders\Word;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;

class Factory
{
    use Macroable;

    public function __construct(protected Container $container)
    {
    }

    public function create(string $type): TextLoader
    {
        return match ($type) {
            'html' => $this->container->make(Html::class),
            'pdf' => $this->container->make(Pdf::class),
            'text' => $this->container->make(Text::class),
            'rtf' => $this->container->make(Rtf::class),
            'textract_s3' => $this->container->make(TextractUsingS3Upload::class),
            'textract' => $this->container->make(Textract::class),
            'web' => $this->container->make(Web::class),
            'word' => $this->container->make(Word::class),
            default => throw new InvalidArgumentException("Invalid text loader type: $type"),
        };
    }

    // Convenience Methods
    public function html(mixed $data): ?TextContent
    {
        return $this->create('html')->load($data);
    }

    public function pdf(mixed $data): ?TextContent
    {
        return $this->create('pdf')->load($data);
    }

    public function text(mixed $data): ?TextContent
    {
        return $this->create('text')->load($data);
    }

    public function rtf(mixed $data): ?TextContent
    {
        return $this->create('rtf')->load($data);
    }

    public function textractUsingS3Upload(mixed $data): ?TextContent
    {
        return $this->create('textract_s3')->load($data);
    }

    public function textract(mixed $data): ?TextContent
    {
        return $this->create('textract')->load($data);
    }

    public function web(mixed $data): ?TextContent
    {
        return $this->create('web')->load($data);
    }

    public function word(mixed $data): ?TextContent
    {
        return $this->create('word')->load($data);
    }
}
