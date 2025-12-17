<?php

declare(strict_types=1);

namespace HelgeSverre\Extractor\Text;

use Illuminate\Support\Str;
use Illuminate\Support\Stringable as LaravelStringable;
use Stringable;

class TextContent implements Stringable
{
    protected string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public static function make(string $content): self
    {
        return new self($content);
    }

    public function normalized(): string
    {
        return Utils::normalizeWhitespace($this->content);
    }

    public function asStringable(): LaravelStringable
    {
        return Str::of($this->content);
    }

    public function toString(): string
    {
        return $this->content;
    }

    public function __toString()
    {
        return $this->toString();
    }
}
