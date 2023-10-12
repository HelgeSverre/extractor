<?php

namespace HelgeSverre\Extractor\Enums;

enum Model: string
{
    // Good, Faster
    case TURBO_INSTRUCT = 'gpt-3.5-turbo-instruct';

    // Decent, fast-ish
    case TURBO_16K = 'gpt-3.5-turbo-16k';
    case TURBO = 'gpt-3.5-turbo';

    // Smarter, slower
    case GPT4 = 'gpt-4';
    case GPT4_32K = 'gpt-4-32k';

    public function isCompletion(): bool
    {
        return match ($this) {
            self::TURBO_INSTRUCT => true,
            default => false,
        };
    }
}
