<?php

namespace HelgeSverre\Extractor\Extraction;

use HelgeSverre\Extractor\Text\TextContent;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use OpenAI\Responses\Chat\CreateResponse as ChatResponse;
use OpenAI\Responses\Completions\CreateResponse as CompletionResponse;

abstract class Extractor
{
    protected array $preprocessors = [];

    protected array $processors = [];

    public function __construct(protected array $config = [])
    {
        $this->boot();
    }

    public function model(): ?string
    {
        return Arr::get($this->config, 'model');
    }

    public function maxTokens(): ?int
    {
        return Arr::get($this->config, 'max_tokens');
    }

    public function temperature(): ?float
    {
        return Arr::get($this->config, 'temperature');
    }

    protected function boot(): void
    {
        foreach (class_uses_recursive($this) as $trait) {
            if (method_exists($this, $method = 'boot'.class_basename($trait))) {
                call_user_func([$this, $method]);
            }
        }
    }

    public function registerPreprocessor(callable $callback): self
    {
        $this->preprocessors[] = $callback;

        return $this;
    }

    public function registerProcessor(callable $callback): self
    {
        $this->processors[] = $callback;

        return $this;
    }

    public function name(): string
    {
        return Str::slug(class_basename(get_class($this)));
    }

    public function prompt(string|TextContent $input): string
    {
        return $this->view(array_merge(['input' => $input], $this->config));
    }

    public function view($input): View
    {
        return view("extractor::{$this->name()}", $input);
    }

    public function preprocess(TextContent|string $input): string
    {
        foreach ($this->preprocessors as $preprocessor) {
            $input = $preprocessor($input, $this);
        }

        return $input;
    }

    public function process(string $response): mixed
    {
        foreach ($this->processors as $processor) {
            $response = $processor($response, $this);
        }

        return $response;
    }

    public function processResponse(ChatResponse|CompletionResponse $response): ChatResponse|CompletionResponse
    {
        return $response;
    }
}
