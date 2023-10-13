<?php

namespace HelgeSverre\Extractor;

use HelgeSverre\Extractor\Enums\Model;
use HelgeSverre\Extractor\Text\TextContent;

class ExtractorManager
{
    protected array $extractors = [];

    public function extend(string $name, callable $callback): void
    {
        $this->extractors[$name] = $callback;
    }

    /**
     * @throws \Exception
     */
    public function extract(
        string $nameOrClass,
        TextContent|string $input,
        Model $model = null,
        int $maxTokens = null,
        float $temperature = null
    ): ?array {
        // If the given name is an alias registered with extend(), use it.
        if (isset($this->extractors[$nameOrClass])) {
            $extractor = call_user_func($this->extractors[$nameOrClass]);
        } // Otherwise, assume it's a direct class name.
        else {
            if (! class_exists($nameOrClass)) {
                throw new \Exception("Extractor class [$nameOrClass] not found.");
            }
            $extractor = app($nameOrClass); // or new $nameOrClass(), but using app() for potential DI
        }

        $engine = new Engine($extractor);

        return $engine->run(
            input: $input,
            model: $model,
            maxTokens: $maxTokens,
            temperature: $temperature
        );
    }
}
