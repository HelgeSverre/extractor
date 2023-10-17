<?php

namespace HelgeSverre\Extractor\Extraction;

use HelgeSverre\Extractor\Exceptions\InvalidJsonReturnedError;
use HelgeSverre\Extractor\Text\TextContent;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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

    public function registerPreprocessor(callable $callback, int $priority = 100): self
    {
        $this->preprocessors[] = ['callback' => $callback, 'priority' => $priority];
        usort($this->preprocessors, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        return $this;
    }

    public function registerProcessor(callable $callback, int $priority = 100): self
    {
        $this->processors[] = ['callback' => $callback, 'priority' => $priority];
        usort($this->processors, fn ($a, $b) => $a['priority'] <=> $b['priority']);

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
        foreach (Arr::pluck($this->preprocessors, 'callback') as $preprocessor) {
            $input = $preprocessor($input, $this);
        }

        return $input;
    }

    public function throwsOnInvalidJsonResponse(): bool
    {
        return true;
    }

    public function decodeResponse($response)
    {
        $decoded = json_decode($response, true);

        if ($decoded === null && $this->throwsOnInvalidJsonResponse()) {
            throw new InvalidJsonReturnedError("Invalid JSON returned:\n$response");
        }

        return $decoded;
    }

    public function process($response): mixed
    {
        $response = $this->decodeResponse($response);

        foreach (Arr::pluck($this->processors, 'callback') as $processor) {
            $response = $processor($response, $this);
        }

        return $response;
    }

    public function mergeConfig(array $config): self
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }
}
