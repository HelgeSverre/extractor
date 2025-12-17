<?php

declare(strict_types=1);

namespace HelgeSverre\Extractor\Extraction;

use HelgeSverre\Extractor\Extraction\Concerns\DecodesResponse;
use HelgeSverre\Extractor\Text\TextContent;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;

abstract class Extractor
{
    use Conditionable;
    use DecodesResponse;

    protected array $preprocessors = [];

    protected array $processors = [];

    public function __construct(protected array $config = [])
    {
        $this->boot();
    }

    public function model(): ?string
    {
        return $this->config('model');
    }

    public function maxTokens(): ?int
    {
        return $this->config('max_tokens');
    }

    public function temperature(): ?float
    {
        return $this->config('temperature');
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

    public function prepareInput(array $input): array
    {
        return $input;
    }

    public function prompt(string|TextContent $input): string
    {
        return $this->view($this->prepareInput(array_merge(['input' => $input], $this->config)))->render();
    }

    public function view(array $input): View
    {
        return view($this->viewName(), $input);
    }

    public function viewName(): string
    {
        return "extractor::{$this->name()}";
    }

    public function name(): string
    {
        return Str::slug(class_basename(get_class($this)));
    }

    public function preprocess(TextContent|string $input): mixed
    {
        foreach (Arr::pluck($this->preprocessors, 'callback') as $preprocessor) {
            $input = $preprocessor($input, $this);
        }

        return $input;
    }

    public function process($response): mixed
    {
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

    public function config($key, $default = null)
    {
        return Arr::get($this->config, $key, $default);
    }

    public function addConfig(string $key, $value): self
    {
        $this->config[$key] = $value;

        return $this;
    }
}
