<?php

namespace HelgeSverre\Extractor\Text\Loaders\Textract\Data;

final class Block
{
    const TABLE = 'TABLE';

    const CELL = 'CELL';

    const PAGE = 'PAGE';

    const WORD = 'WORD';

    const LINE = 'LINE';

    const KEY_VALUE_SET = 'KEY_VALUE_SET';

    const SELECTION_ELEMENT = 'SELECTION_ELEMENT';

    const KEY = 'KEY';

    const VALUE = 'VALUE';

    public static function isTable($block): bool
    {
        return $block['BlockType'] === self::TABLE;
    }

    public static function isPage($block): bool
    {
        return $block['BlockType'] === self::PAGE;
    }

    public static function isWord($block): bool
    {
        return $block['BlockType'] === self::WORD;
    }

    public static function isLine($block): bool
    {
        return $block['BlockType'] === self::LINE;
    }

    public static function isKeyValueSet($block): bool
    {
        return $block['BlockType'] === self::KEY_VALUE_SET;
    }

    public static function isCell($block): bool
    {
        return $block['BlockType'] === self::CELL;
    }

    public static function isSelectionElement($block): bool
    {
        return $block['BlockType'] === self::SELECTION_ELEMENT;
    }

    public static function boundingBox($block): array
    {
        return $block['Geometry']['BoundingBox'];
    }

    public static function relationshipIds($block): ?array
    {
        return $block['Relationships'][0]['Ids'];
    }

    public static function hasRelationships($block): bool
    {
        return isset($block['Relationships']);
    }

    public static function onlyLineBlocks(array $blocks)
    {
        return array_filter($blocks, fn ($block) => self::isLine($block));
    }

    public static function onlyWordBlocks(mixed $blocks)
    {
        return array_filter($blocks, fn ($block) => self::isWord($block));
    }

    public static function blockWhereRelationshipIdsAreExactMatch(array $blocks, array $childIds): ?array
    {
        foreach ($blocks as $lineBlock) {
            if (self::relationshipIds($lineBlock) == $childIds) {
                return $lineBlock;
            }
        }

        return null;
    }

    public static function blocksWhereRelationshipIdsContainsAny(array $lineBlocks, ?array $childIds): array
    {
        $lines = [];

        foreach ($lineBlocks as $lineBlock) {
            if (! array_diff(self::relationshipIds($lineBlock), $childIds)) {
                $lines[] = $lineBlock;
            }
        }

        return $lines;
    }

    public static function isEntityTypeKey(array $block): bool
    {
        return $block['EntityTypes'][0] === self::KEY;
    }

    public static function isEntityTypeValue(array $block): bool
    {
        return $block['EntityTypes'][0] === self::VALUE;
    }

    public static function combineText(array $blocks): string
    {
        return implode(' ', array_column($blocks, 'Text'));
    }

    public static function blocksWithinBoundingBox(array $blocks, mixed $boundingBox): array
    {
        $matches = [];

        foreach ($blocks as $block) {
            if (self::aabb(self::boundingBox($block), $boundingBox)) {
                $matches[] = $block;
            }
        }

        return $matches;
    }

    /**
     * Axis Aligned Bounding Box
     *
     * @see https://developer.mozilla.org/en-US/docs/Games/Techniques/2D_collision_detection
     *
     * @return bool true if intersects, false if not
     */
    public static function aabb(array $a, array $b): bool
    {
        return $a['Left'] < $b['Left'] + $b['Width']
            && $b['Left'] < $a['Left'] + $a['Width']
            && $a['Top'] < $b['Top'] + $b['Height']
            && $b['Top'] < $a['Height'] + $a['Top'];
    }

    public static function resolveText(array $blocks, array $blockIds): ?string
    {
        $words = [];
        $blocksById = array_column($blocks, null, 'Id');

        foreach ($blockIds as $blockId) {
            $currentBlock = $blocksById[$blockId];

            switch ($currentBlock['BlockType']) {
                case self::LINE:
                case self::WORD:
                    $words[] = $currentBlock['Text'];
                    break;

                case self::TABLE:
                case self::CELL:
                case self::KEY_VALUE_SET:
                    if (self::hasRelationships($currentBlock)) {
                        $words[] = self::resolveText($blocks, self::relationshipIds($currentBlock));
                    }
                    break;

                case self::SELECTION_ELEMENT:
                    // Ignored
                    break;
            }
        }

        return implode(' ', $words);
    }
}
