<?php

namespace HelgeSverre\Extractor;

use Exception;
use HelgeSverre\Extractor\Contracts\Engine;
use HelgeSverre\Extractor\Extraction\Builtins\Fields;
use HelgeSverre\Extractor\Extraction\Builtins\Simple;
use HelgeSverre\Extractor\Extraction\Extractor;
use HelgeSverre\Extractor\Text\ImageContent;
use HelgeSverre\Extractor\Text\TextContent;

class ExtractorManager
{
    protected array $extractors = [];

    public function __construct(protected Engine $engine)
    {

    }

    public function extend(string $name, callable $callback): void
    {
        $this->extractors[$name] = $callback;
    }

    public function extract(
        string|Extractor $nameOrClass,
        TextContent|string $input,
        ?array $config = null,
        string $model = 'gpt-3.5-turbo-1106',
        int $maxTokens = 2000,
        float $temperature = 0.1,
    ): mixed {
        $extractor = $this->resolveExtractor($nameOrClass);

        if ($config) {
            $extractor->mergeConfig($config);
        }

        return $this->engine->run(
            extractor: $extractor,
            input: $input,
            model: $model ?? $extractor->model(),
            maxTokens: $maxTokens ?? $extractor->maxTokens(),
            temperature: $temperature ?? $extractor->temperature(),
        );
    }

    public function view(
        string $view,
        TextContent|string $input,
        ?array $config = null,
        string $model = 'gpt-3.5-turbo-1106',
        int $maxTokens = 2000,
        float $temperature = 0.1,
    ): mixed {
        $extractor = new Simple(array_merge($config, [
            'view' => $view,
        ]));

        return $this->engine->run(
            extractor: $extractor,
            input: $input,
            model: $model ?? $extractor->model(),
            maxTokens: $maxTokens ?? $extractor->maxTokens(),
            temperature: $temperature ?? $extractor->temperature(),
        );
    }

    public function fields(
        ImageContent|TextContent|string $input,
        array $fields,
        ?array $config = null,
        string $model = 'gpt-3.5-turbo-1106',
        int $maxTokens = 2000,
        float $temperature = 0.1,
    ): mixed {
        $extractor = $this->resolveExtractor(Fields::class);

        if ($config) {
            $extractor->mergeConfig($config);
        }

        $extractor->addConfig('fields', $fields);

        return $this->engine->run(
            extractor: $extractor,
            input: $input,
            model: $model ?? $extractor->model(),
            maxTokens: $maxTokens ?? $extractor->maxTokens(),
            temperature: $temperature ?? $extractor->temperature(),
        );
    }

    protected function resolveExtractor(string|Extractor $nameOrClass): Extractor
    {
        // If it's already an instance of Extractor, return it.
        if ($nameOrClass instanceof Extractor) {
            return $nameOrClass;
        }

        // If the given name is an alias registered with extend(), use it.
        if (isset($this->extractors[$nameOrClass])) {
            return call_user_func($this->extractors[$nameOrClass]);
        }

        // Otherwise, assume it's a direct class name.
        if (! class_exists($nameOrClass)) {
            throw new Exception("Extractor class [$nameOrClass] not found.");
        }

        // Try to resolve it from the container.
        return app($nameOrClass);
    }
}
