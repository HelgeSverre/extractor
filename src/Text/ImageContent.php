<?php

declare(strict_types=1);

namespace HelgeSverre\Extractor\Text;

use BrandEmbassy\FileTypeDetector\Detector;

class ImageContent extends TextContent
{
    const TYPE_URL = 'url';

    const TYPE_FILE = 'file';

    const TYPE_RAW = 'raw';

    /**
     * Valid detail levels for the OpenAI vision API.
     *
     * - "low"      Fast, low-cost; model receives a 512×512px version.
     * - "high"     Standard high-fidelity image understanding.
     * - "original" Large, dense, spatially sensitive images (gpt-5.4+).
     * - "auto"     Let the model choose the detail level (default).
     */
    const VALID_DETAIL_LEVELS = ['low', 'high', 'original', 'auto'];

    public function __construct(
        protected string $content,
        protected string $type,
        protected ?string $mime = null,
        protected string $detail = 'auto',
    ) {
        parent::__construct($this->content);
    }

    public static function url(string $url, string $detail = 'auto'): self
    {
        return new self($url, self::TYPE_URL, detail: $detail);
    }

    public static function file(string $path, ?string $mime = null, string $detail = 'auto'): self
    {
        return new self($path, self::TYPE_FILE, $mime, $detail);
    }

    public static function raw(string $rawImageContents, ?string $mime = null, string $detail = 'auto'): self
    {
        return new self($rawImageContents, self::TYPE_RAW, $mime, $detail);
    }

    public function detail(): string
    {
        return $this->detail;
    }

    public function isUrl(): bool
    {
        return $this->type === self::TYPE_URL;
    }

    public function isBase64able(): bool
    {
        return $this->isFile() || $this->isRaw();
    }

    public function isFile(): bool
    {
        return $this->type === self::TYPE_FILE;
    }

    public function isRaw(): bool
    {
        return $this->type === self::TYPE_RAW;
    }

    public function content(): ?string
    {
        return $this->content;
    }

    public function imageData(): ?string
    {
        return match ($this->type) {
            self::TYPE_FILE, self::TYPE_URL => file_get_contents($this->content),
            self::TYPE_RAW => $this->content,
        };
    }

    public function type(): string
    {
        return $this->type;
    }

    public function toBase64(): string
    {
        return base64_encode($this->imageData());
    }

    public function toBase64Url(): string
    {
        return sprintf('data:%s;base64,%s', $this->mime(), $this->toBase64());
    }

    public function mime(): ?string
    {
        return $this->mime ?? $this->guessMime();
    }

    protected function guessMime(): ?string
    {
        if ($this->isRaw()) {
            return rescue(fn () => Detector::detectFromContent($this->content)?->getMimeType());
        }

        if ($this->isFile()) {
            return rescue(fn () => Detector::detectByContent($this->content)?->getMimeType());
        }

        // NOTE: Mime type is irrelevant for type "url", as it will be auto-detected by OpenAI.
        return null;
    }
}
