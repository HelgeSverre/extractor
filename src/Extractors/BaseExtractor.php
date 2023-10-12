<?php

namespace HelgeSverre\Extractor\Extractors;

use HelgeSverre\Extractor\Contracts\Extractor;
use HelgeSverre\Extractor\Prompt;
use HelgeSverre\Extractor\TextContent;

abstract class BaseExtractor implements Extractor
{
    public function __construct(protected ?array $config = [])
    {
    }

    abstract public function name(): string;

    public function preprocess(TextContent|string $input): string
    {
        return $input;
    }

    public function prompt(string|TextContent $input): string
    {
        return Prompt::load($this->name(), [
            'input' => $input,
            'config' => $this->config,
        ]);
    }

    public function process(string $response): mixed
    {
        return $response;
    }

    public function model(): ?string
    {
        return null;
    }

    public function maxTokens(): ?int
    {
        return null;
    }

    public function temperature(): ?float
    {
        return null;
    }
}
