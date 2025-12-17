<?php

declare(strict_types=1);

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
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;

class Factory
{
    use Macroable;

    public function __construct(protected Container $container) {}

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

    public function fromMime(string $mime, mixed $content): ?TextContent
    {
        return match (true) {
            blank($content) => null,
            Str::contains($mime, 'image') => rescue(
                callback: fn () => $this->textract($content),
                rescue: fn () => $this->textractUsingS3Upload($content)
            ),
            Str::contains($mime, 'pdf') => rescue(
                callback: fn () => $this->pdf($content),
                rescue: fn () => $this->textractUsingS3Upload($content)
            ),
            Str::contains($mime, ['xml', 'html']) => $this->html($content),
            Str::contains($mime, 'text/plain') => $this->text($content),
            Str::contains($mime, 'text/rtf') => $this->rtf($content),

            // Not commonly used, but let's use it anyways.
            Str::contains($mime, 'text/x-uri') => $this->web($content),

            // Stolen from: https://stackoverflow.com/questions/4212861/what-is-a-correct-mime-type-for-docx-pptx-etc
            in_array($mime, [
                'application/msword',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
                'application/vnd.ms-word.document.macroEnabled.12',
                'application/vnd.ms-word.template.macroEnabled.12',
            ]) => $this->word($content),

            default => $this->textractUsingS3Upload($content)
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
