<?php

namespace HelgeSverre\Extractor;

use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

/**
 * I know this looks kind of fucked.
 *
 * This class is based on a lot of trial and error when scraping a _lot_ of receipt emails,
 * however the dataset was LARGELY scandinavian, so this is geared heavily towards that kind of number format.
 */
class NumberParser
{
    public static function parse($text): float|int|null
    {
        // Very deterministic "parsing" into correct datatype stuff.
        if (! config('receipt-scanner.use_forgiving_number_parser', true)) {
            return match (true) {
                is_int($text) => (int) $text,
                is_float($text) => (float) $text,
                is_numeric($text) && in_array($text, [',', '.']) => floatval($text),
                is_numeric($text) && ! in_array($text, [',', '.']) => intval($text),
                default => null  // ¯\_(ツ)_/¯
            };
        }

        if ($text === null) {
            return null;
        }

        if ($text instanceof Stringable) {
            $text = (string) $text;
        }

        if (is_float($text) || is_int($text)) {
            return $text;
        }

        if (is_numeric($text)) {
            if (Str::contains($text, '.')) {
                $decimal = Str::after($text, '.');
                // if there is 2 or fewer decimals after the period, parse it as a regular number.
                if (strlen($decimal) <= 2) {
                    return floatval($text);
                }
            }
        }

        $number = $text;
        $number = mb_strtolower($number);
        $number = preg_replace('/[^0-9,.-]+/', '', $number);

        // Remove currency suffix
        $number = str_replace(',-', '', $number);

        $thousandSeparator = '.';
        $decimalSeparator = ',';

        $number = trim($number, $thousandSeparator.$decimalSeparator);

        // If the number contains both decimal separators
        if (Str::containsAll($number, [',', '.'])) {
            $pointPos = strpos($number, '.');
            $commaPos = strpos($number, ',');

            // , is first
            if ($pointPos > $commaPos) {
                $thousandSeparator = ',';
                $decimalSeparator = '.';
            }

            $number = str_replace($thousandSeparator, '', $number); // Remove thousand separator
            $number = str_replace($decimalSeparator, '.', $number); // Change decimal separator to .
        }

        // Trim trailing minus symbol
        $number = rtrim($number, '-');

        // ASSUMPTION that is only relevant in our ecommerce case.
        // If there is only 1 decimal, and the char count on the right is more than the one
        // on the left, assume it to be a thousand separator instead of a decimal separator
        if (Str::substrCount($number, '.') === 1) {
            [$left, $right] = explode('.', $number);

            if (strlen($left) < strlen($right)) {
                $number = $left.$right;
            }
        }

        // Remove leading/trailing separators, also trim out spaces
        $number = str_replace(' ', '', $number);
        $number = trim($number, $thousandSeparator.$decimalSeparator);
        $number = str_replace(' ', '', $number);
        $number = str_replace(',', '.', $number); // Change decimal separator to .

        if (is_numeric($number)) {
            return $number + 0;
        }

        return 0;
    }

    public static function parseInt($text): int
    {
        return (int) self::parse($text);
    }
}
