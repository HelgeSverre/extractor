<?php

namespace HelgeSverre\Extractor\Contracts;

use HelgeSverre\Extractor\TextContent;
use OpenAI\Responses\Chat\CreateResponse as ChatResponse;
use OpenAI\Responses\Completions\CreateResponse as CompletionResponse;

interface Extractor
{
    public function name(): string;

    public function prompt(string|TextContent $input): string;

    public function preprocess(string|TextContent $input): string;

    // public function processRaw(ChatResponse|CompletionResponse $response): mixed;

    public function process(string $response): mixed;

    public function model(): ?string;

    public function maxTokens(): ?int;

    public function temperature(): ?float;
}
