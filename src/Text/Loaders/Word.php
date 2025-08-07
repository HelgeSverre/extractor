<?php

namespace HelgeSverre\Extractor\Text\Loaders;

use HelgeSverre\Extractor\Contracts\TextLoader;
use HelgeSverre\Extractor\Text\TextContent;
use ZipArchive;

class Word implements TextLoader
{
    protected function loadTextFromDocx(string $data): ?string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'receipt_parser_zip_');
        file_put_contents($tempFile, $data);

        $zip = new ZipArchive;

        if ($zip->open($tempFile) !== true) {
            unlink($tempFile);

            return null;
        }

        $xmlIndex = $zip->locateName('word/document.xml');

        if ($xmlIndex === false) {
            $zip->close();
            unlink($tempFile);

            return null;
        }

        $replacements = [
            // Replace <w:p> tags with newlines
            '/<w:p w[0-9-Za-z]+:[a-zA-Z0-9]+="[a-zA-z"0-9 :="]+">/' => "\n\r",

            // Replace <w:tr> tags with newlines
            '/<w:tr>/' => "\n\r",

            // Replace <w:tab/> tags with tabs
            '/<w:tab\/>/' => "\t",

            // Replace </w:p> tags with newlines
            '/<\/w:p>/' => "\n\r",
        ];

        $replacedData = preg_replace(
            pattern: array_keys($replacements),
            replacement: array_values($replacements),
            subject: $zip->getFromIndex($xmlIndex)
        );

        $zip->close();
        unlink($tempFile);

        return strip_tags($replacedData);

    }

    protected function loadTextFromDoc($data): ?string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'word_doc_parser_');
        file_put_contents($tempFile, $data);

        try {
            // Method 1: Try antiword (most reliable for .doc files)
            $text = $this->loadTextFromDocUsingAntiword($tempFile);
            if ($text !== null && trim($text) !== '') {
                return trim($text);
            }

            // Method 2: Try catdoc as fallback
            $text = $this->loadTextFromDocUsingCatdoc($tempFile);
            if ($text !== null && trim($text) !== '') {
                return trim($text);
            }

            // Method 3: Try PHPWord if available
            $text = $this->loadTextFromDocUsingPhpWord($tempFile);
            if ($text !== null && trim($text) !== '') {
                return trim($text);
            }

            // Method 4: Fallback to original naive method
            return $this->loadTextFromDocNaive($data);
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    protected function loadTextFromDocUsingAntiword(string $filePath): ?string
    {
        if (! $this->isCommandAvailable('antiword')) {
            return null;
        }

        $escapedPath = escapeshellarg($filePath);
        $command = "antiword {$escapedPath} 2>/dev/null";

        $output = shell_exec($command);

        return $output ? trim($output) : null;
    }

    protected function loadTextFromDocUsingCatdoc(string $filePath): ?string
    {
        if (! $this->isCommandAvailable('catdoc')) {
            return null;
        }

        $escapedPath = escapeshellarg($filePath);
        $command = "catdoc {$escapedPath} 2>/dev/null";

        $output = shell_exec($command);

        return $output ? trim($output) : null;
    }

    protected function loadTextFromDocUsingPhpWord(string $filePath): ?string
    {
        if (! class_exists('\PhpOffice\PhpWord\IOFactory')) {
            return null;
        }

        try {
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
            $text = '';

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . "\n";
                    } elseif (method_exists($element, 'getElements')) {
                        foreach ($element->getElements() as $subElement) {
                            if (method_exists($subElement, 'getText')) {
                                $text .= $subElement->getText() . "\n";
                            }
                        }
                    }
                }
            }

            return $text ? trim($text) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function loadTextFromDocNaive($data): ?string
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

    protected function isCommandAvailable(string $command): bool
    {
        $path = shell_exec("which {$command} 2>/dev/null");

        return ! empty($path) && trim($path) !== '';
    }

    public function load(mixed $data): ?TextContent
    {
        // TODO(27 May 2023) ~ Helge: Detect filetype by magic file header or something
        $text = $this->loadTextFromDocx($data) ?? $this->loadTextFromDoc($data);

        return $text ? new TextContent($text) : null;
    }
}
