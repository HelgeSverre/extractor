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
        // TODO(27 May 2023) ~ Helge: Detect filetype by magic file header or something
        $text = $this->loadTextFromDocx($data) ?? $this->loadTextFromDoc($data);

        return $text ? new TextContent($text) : null;
    }
}
