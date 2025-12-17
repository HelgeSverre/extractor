<?php

declare(strict_types=1);

namespace HelgeSverre\Extractor\Extraction\Builtins;

use HelgeSverre\Extractor\Extraction\Extractor;
use HelgeSverre\Extractor\Text\ImageContent;
use Illuminate\Contracts\View\View;

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

    public function view($input): View
    {
        if ($input['input'] instanceof ImageContent) {
            return view('extractor::fields-vision', $input);
        }

        return view('extractor::fields', $input);
    }
}
