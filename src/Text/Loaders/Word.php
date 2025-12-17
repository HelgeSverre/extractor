<?php

declare(strict_types=1);

namespace HelgeSverre\Extractor\Text\Loaders;

use Exception;
use HelgeSverre\Extractor\Contracts\TextLoader;
use HelgeSverre\Extractor\Text\TextContent;
use InvalidArgumentException;
use PhpOffice\PhpWord\IOFactory;
use RuntimeException;
use ZipArchive;

class Word implements TextLoader
{
    protected const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50MB

    protected function loadTextFromDocx(string $data): ?string
    {
        $this->validateSize($data);

        $tempFile = tempnam(sys_get_temp_dir(), 'extractor_docx_');

        if ($tempFile === false) {
            throw new RuntimeException('Failed to create temporary file');
        }

        try {
            if (file_put_contents($tempFile, $data) === false) {
                throw new RuntimeException('Failed to write temporary file');
            }

            $zip = new ZipArchive;

            if ($zip->open($tempFile) !== true) {
                return null;
            }

            try {
                $xmlIndex = $zip->locateName('word/document.xml');

                if ($xmlIndex === false) {
                    return null;
                }

                $replacements = [
                    '/<w:p w[0-9-Za-z]+:[a-zA-Z0-9]+="[a-zA-z"0-9 :="]+">/' => "\n\r",
                    '/<w:tr>/' => "\n\r",
                    '/<w:tab\/>/' => "\t",
                    '/<\/w:p>/' => "\n\r",
                ];

                $replacedData = preg_replace(
                    pattern: array_keys($replacements),
                    replacement: array_values($replacements),
                    subject: $zip->getFromIndex($xmlIndex)
                );

                return strip_tags($replacedData);
            } finally {
                $zip->close();
            }
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    protected function loadTextFromDoc(mixed $data): ?string
    {
        if (! is_string($data)) {
            return null;
        }

        $this->validateSize($data);

        $tempFile = tempnam(sys_get_temp_dir(), 'extractor_doc_');

        if ($tempFile === false) {
            throw new RuntimeException('Failed to create temporary file');
        }

        try {
            if (file_put_contents($tempFile, $data) === false) {
                throw new RuntimeException('Failed to write temporary file');
            }

            $text = $this->loadTextFromDocUsingPhpWord($tempFile);
            if ($text !== null && trim($text) !== '') {
                return trim($text);
            }

            return $this->loadTextFromDocNaive($data);
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    protected function loadTextFromDocUsingPhpWord(string $filePath): ?string
    {
        if (! class_exists('\PhpOffice\PhpWord\IOFactory')) {
            return null;
        }

        try {
            $phpWord = IOFactory::load($filePath);
            $text = '';

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText()."\n";
                    } elseif (method_exists($element, 'getElements')) {
                        foreach ($element->getElements() as $subElement) {
                            if (method_exists($subElement, 'getText')) {
                                $text .= $subElement->getText()."\n";
                            }
                        }
                    }
                }
            }

            return $text !== '' ? trim($text) : null;
        } catch (Exception $e) {
            return null;
        }
    }

    protected function loadTextFromDocNaive(string $data): ?string
    {
        $text = '';
        $lines = explode(chr(0x0D), $data);

        foreach ($lines as $currentLine) {
            if (! str_contains($currentLine, chr(0x00)) && strlen($currentLine) !== 0) {
                $text .= $currentLine.' ';
            }
        }

        return preg_replace('/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/', '', $text) ?: null;
    }

    public function load(mixed $data): ?TextContent
    {
        if (! is_string($data)) {
            throw new InvalidArgumentException('Word loader expects string data');
        }

        $text = $this->loadTextFromDocx($data) ?? $this->loadTextFromDoc($data);

        return $text !== null ? new TextContent($text) : null;
    }

    protected function validateSize(string $data): void
    {
        if (strlen($data) > self::MAX_FILE_SIZE) {
            throw new InvalidArgumentException(
                'Document size exceeds maximum of '.self::MAX_FILE_SIZE.' bytes'
            );
        }
    }
}
