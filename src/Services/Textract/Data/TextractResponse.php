<?php

namespace HelgeSverre\Extractor\Services\Textract\Data;

use Aws\Result;
use Illuminate\Support\Arr;

class TextractResponse
{
    protected ?string $text = null;

    protected array $raw = [];

    protected array $forms = [];

    protected array $tables = [];

    protected array $metaData = [];

    public static function fromAwsResult(Result $result): ?self
    {
        return self::fromArray($result->toArray());
    }

    public static function fromJson(string $json): ?self
    {
        return self::fromArray(json_decode($json, true));
    }

    public static function fromArray(array $array): ?self
    {
        $response = new self();
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

    public function getForms(): array
    {
        return $this->forms;
    }

    public function getFormAsArray(): array
    {
        return collect($this->forms)
            ->mapWithKeys(fn ($item) => [$item['key'] => $item['value']])
            ->toArray();
    }

    public function getTables(): array
    {
        return $this->tables;
    }

    public function getRaw(): array
    {
        return $this->raw;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function getRawJson(): string
    {
        return json_encode($this->raw);
    }

    public function getWordBlocks(): array
    {
        $blocks = Arr::get($this->raw, 'Blocks', []);

        $words = [];

        foreach (Block::onlyWordBlocks($blocks) as $block) {
            $words[] = [
                'text' => $block['Text'],
                'box' => [
                    'width' => $block['Geometry']['BoundingBox']['Width'],
                    'height' => $block['Geometry']['BoundingBox']['Height'],
                    'top' => $block['Geometry']['BoundingBox']['Top'],
                    'left' => $block['Geometry']['BoundingBox']['Left'],
                ],
            ];
        }

        return $words;
    }

    public function getLineBlocks(): array
    {
        $blocks = Arr::get($this->raw, 'Blocks', []);

        $words = [];

        foreach (Block::onlyLineBlocks($blocks) as $block) {
            $words[] = [
                'text' => $block['Text'],
                'box' => [
                    'width' => $block['Geometry']['BoundingBox']['Width'],
                    'height' => $block['Geometry']['BoundingBox']['Height'],
                    'top' => $block['Geometry']['BoundingBox']['Top'],
                    'left' => $block['Geometry']['BoundingBox']['Left'],
                ],
            ];
        }

        return $words;
    }
}
