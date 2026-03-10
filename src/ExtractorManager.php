<?php

declare(strict_types=1);

namespace HelgeSverre\Extractor;

use Exception;
use HelgeSverre\Extractor\Extraction\Builtins\Fields;
use HelgeSverre\Extractor\Extraction\Builtins\Simple;
use HelgeSverre\Extractor\Extraction\Extractor;
use HelgeSverre\Extractor\Text\ImageContent;
use HelgeSverre\Extractor\Text\TextContent;

class ExtractorManager
{
    protected array $extractors = [];

    public function __construct(protected Engine $engine) {}

    public function extend(string $name, callable $callback): void
    {
        $this->extractors[$name] = $callback;
    }

    public function extract(
        string|Extractor $nameOrClass,
        TextContent|string $input,
        ?array $config = null,
        ?string $model = null,
        ?int $maxTokens = null,
        ?float $temperature = null,
    ): mixed {
        $extractor = $this->resolveExtractor($nameOrClass);

        if ($config) {
            $extractor->mergeConfig($config);
        }

        return $this->engine->run(
            extractor: $extractor,
            input: $input,
            model: $model ?? $extractor->model() ?? $this->defaultModel(),
            maxTokens: $maxTokens ?? $extractor->maxTokens() ?? 2000,
            temperature: $temperature ?? $extractor->temperature() ?? 0.1,
        );
    }

    public function view(
        string $view,
        TextContent|string $input,
        ?array $config = null,
        ?string $model = null,
        ?int $maxTokens = null,
        ?float $temperature = null,
    ): mixed {
        $extractor = new Simple(array_merge($config ?? [], [
            'view' => $view,
        ]));

        return $this->engine->run(
            extractor: $extractor,
            input: $input,
            model: $model ?? $extractor->model() ?? $this->defaultModel(),
            maxTokens: $maxTokens ?? $extractor->maxTokens() ?? 2000,
            temperature: $temperature ?? $extractor->temperature() ?? 0.1,
        );
    }

    public function fields(
        ImageContent|TextContent|string $input,
        array $fields,
        ?array $config = null,
        ?string $model = null,
        ?int $maxTokens = null,
        ?float $temperature = null,
    ): mixed {
        $extractor = $this->resolveExtractor(Fields::class);

        if ($config) {
            $extractor->mergeConfig($config);
        }

        $extractor->addConfig('fields', $fields);

        return $this->engine->run(
            extractor: $extractor,
            input: $input,
            model: $model ?? $extractor->model() ?? $this->defaultModel(),
            maxTokens: $maxTokens ?? $extractor->maxTokens() ?? 2000,
            temperature: $temperature ?? $extractor->temperature() ?? 0.1,
        );
    }

    protected function defaultModel(): string
    {
        return config('extractor.model', 'gpt-4o-mini');
    }

    protected function resolveExtractor(string|Extractor $nameOrClass): Extractor
    {
        if ($nameOrClass instanceof Extractor) {
            return $nameOrClass;
        }

        if (isset($this->extractors[$nameOrClass])) {
            return call_user_func($this->extractors[$nameOrClass]);
        }

        if (! class_exists($nameOrClass)) {
            throw new Exception("Extractor class [$nameOrClass] not found.");
        }

        return app($nameOrClass);
    }
}
