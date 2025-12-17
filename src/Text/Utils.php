<?php

declare(strict_types=1);

namespace HelgeSverre\Extractor\Text;

use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class Utils
{
    public static function normalizeWhitespace(string $text): string
    {
        return Str::of($text)->squish()->trim()->toString();
    }

    public static function cleanHtml(
        string $html,
        array $elementsToRemove = ['script', 'style', 'link', 'head', 'noscript', 'template', 'svg', 'br', 'hr'],
        bool $normalizeWhitespace = true
    ): string {
        $inputHtml = $normalizeWhitespace
            ? Str::of($html)
                ->replace('<', ' <')
                ->replace('>', '> ')
                ->toString()
            : $html;

        $crawler = new Crawler($inputHtml);

        foreach ($elementsToRemove as $element) {
            $crawler->filter($element)->each(function (Crawler $node) {
                return $node->getNode(0)->parentNode->removeChild($node->getNode(0));
            });
        }

        return $normalizeWhitespace ? self::normalizeWhitespace($crawler->text('')) : $crawler->text('');
    }
}
