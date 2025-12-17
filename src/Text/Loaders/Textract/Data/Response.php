<?php

declare(strict_types=1);

namespace HelgeSverre\Extractor\Text\Loaders\Textract\Data;

use Aws\Result;
use Illuminate\Support\Arr;

class Response
{
    public function __construct(
        protected array $raw = [],
        protected ?string $text = null,
        protected array $forms = [],
        protected array $tables = [],
        protected array $metaData = [],
    ) {}

    public static function fromAwsResult(Result $result): ?self
    {
        return self::fromArray($result->toArray());
    }

    public static function fromArray(array $array): ?self
    {
        $response = new self;
        $response->raw = $array;
        $response->metaData['pages'] = Arr::get($array, 'DocumentMetadata.Pages');

        $blocks = Arr::get($array, 'Blocks', []);
        $blocksById = array_column($blocks, null, 'Id');

        $response->text = '';

        foreach ($blocks as $block) {
            if (Block::isPage($block)) {
                $response->text .= Block::resolveText($blocks, $block['Relationships'][0]['Ids']);
            }
        }

        foreach ($blocks as $block) {
            if (Block::isTable($block)) {
                $cells = Arr::only($blocksById, $block['Relationships'][0]['Ids']);
                $table = [];

                foreach ($cells as $cell) {
                    // RowIndex and ColumnIndex is one-based, we want it zero-based
                    $row = $cell['RowIndex'] - 1;
                    $col = $cell['ColumnIndex'] - 1;

                    if (Block::hasRelationships($cell)) {
                        $table[$row][$col] = Block::resolveText($blocks, $cell['Relationships'][0]['Ids']);

                        continue;
                    }

                    $table[$row][$col] = ''; // No content in CELL
                }

                $response->tables[] = $table;
            }

            if (Block::isKeyValueSet($block)) {
                if (Block::isEntityTypeKey($block)) {
                    // No children - bug, skip it.
                    if (isset($block['Relationships'][1]) == false ||
                        isset($block['Relationships'][0]) == false) {
                        continue;
                    }

                    $response->forms[] = [
                        'confidence' => $block['Confidence'],
                        'key' => Block::resolveText($blocks, $block['Relationships'][1]['Ids']),
                        'value' => Block::resolveText($blocks, $block['Relationships'][0]['Ids']),
                    ];
                }
            }
        }

        return $response;
    }

    public function forms(): array
    {
        return $this->forms;
    }

    public function tables(): array
    {
        return $this->tables;
    }

    public function raw(): array
    {
        return $this->raw;
    }

    public function text(): ?string
    {
        return $this->text;
    }

    public function getRawJson(): string
    {
        return json_encode($this->raw);
    }
}
