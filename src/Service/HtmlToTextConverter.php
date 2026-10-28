<?php

namespace Lle\HermesBundle\Service;

use League\HTMLToMarkdown\HtmlConverter;
use Symfony\Component\Mime\HtmlToTextConverter\DefaultHtmlToTextConverter;
use Symfony\Component\Mime\HtmlToTextConverter\HtmlToTextConverterInterface;

/**
 * Generates the text version of a mail from its HTML.
 * Links are kept as [text](url), images (logos...) and bold/italic markers are removed.
 */
class HtmlToTextConverter implements HtmlToTextConverterInterface
{
    public function convert(string $html, string $charset): string
    {
        if (!class_exists(HtmlConverter::class)) {
            return (new DefaultHtmlToTextConverter())->convert($html, $charset);
        }

        $converter = new HtmlConverter([
            'remove_nodes' => 'head style script img',
            'bold_style' => '',
            'italic_style' => '',
            'strip_tags' => true,
            'hard_break' => true,
        ]);

        $text = $converter->convert($html);
        // Links left empty by the removed images (clickable logo)
        $text = (string)preg_replace('/\[\s*\]\([^)]*\)/', '', $text);
        // Blank lines left by the removed images
        $text = (string)preg_replace("/\n{3,}/", "\n\n", $text);

        return trim($text);
    }
}
