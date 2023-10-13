<?php

namespace HelgeSverre\Extractor;

use Exception;
use HelgeSverre\Extractor\Enums\Model;
use HelgeSverre\Extractor\Extraction\Extractor;
use HelgeSverre\Extractor\Text\TextContent;

class ExtractorManager
{
    protected array $extractors = [];

    public function extend(string $name, callable $callback): void
    {
        $this->extractors[$name] = $callback;
    }

    /**
     * @throws Exception
     */
    public function extract(
        string|Extractor $nameOrClass,
        TextContent|string $input,
        Model $model = null,
        int $maxTokens = null,
        float $temperature = null
    ): ?array {
        $extractor = $this->resolveExtractor($nameOrClass);
        $engine = new Engine($extractor);

        return $engine->run(
            input: $input,
            model: $model,
            maxTokens: $maxTokens,
            temperature: $temperature
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
