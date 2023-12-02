<?php

namespace HelgeSverre\Extractor\Extraction\Builtins;

use HelgeSverre\Extractor\Extraction\Extractor;

class Fields extends Extractor
{
    public function fieldsToMarkdownList(array $array, $level = 0): string
    {
        $markdown = '';
        foreach ($array as $key => $value) {
            // Adding indentation based on the level of nesting
            $indent = str_repeat('  ', $level);

            if (is_array($value)) {
                // If the key is a string, display it as a parent item and indent its children
                if (is_string($key)) {
                    $markdown .= "{$indent}- {$key}\n";
                }
                // Recursively process nested array
                $markdown .= $this->fieldsToMarkdownList($value, $level + 1);
            } else {
                // Check if key is a string to format "key - value" else just display the value
                $formattedKey = is_string($key) ? "{$key} - " : '';
                $markdown .= "{$indent}- {$formattedKey}{$value}\n";
            }
        }

        return $markdown;
    }

    public function prepareInput(array $input): array
    {
        $input['fieldList'] = $this->fieldsToMarkdownList($input['fields'] ?? []);

        return $input;
    }

    public function viewName(): string
    {
        return 'extractor::fields';
    }
}
