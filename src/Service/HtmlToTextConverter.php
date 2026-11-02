<?php

namespace Lle\HermesBundle\Service;

use League\HTMLToMarkdown\HtmlConverter;
use Lle\HermesBundle\Service\HtmlToText\BlockConverter;
use Lle\HermesBundle\Service\HtmlToText\LinkConverter;
use Lle\HermesBundle\Service\HtmlToText\PlainTextConverter;
use Symfony\Component\Mime\HtmlToTextConverter\HtmlToTextConverterInterface;

/**
 * Generates the text version of a mail from its HTML.
 * Links are kept as [text](url), images (logos...) and bold/italic markers are removed,
 * text is not markdown-escaped and table cells are rendered as paragraphs.
 */
class HtmlToTextConverter implements HtmlToTextConverterInterface
{
    private ?HtmlConverter $converter = null;

    public function convert(string $html, string $charset): string
    {
        $text = $this->getConverter()->convert($html);
        // Spaces left at line start/end by the whitespace between blocks
        $text = (string)preg_replace('/^[ \t]+|[ \t]+$/m', '', $text);
        // Blank lines left by removed elements and nested blocks
        $text = (string)preg_replace("/\n{3,}/", "\n\n", $text);

        return trim($text);
    }

    /**
     * Built once: the converter is stateless, so it can be reused for every recipient.
     */
    private function getConverter(): HtmlConverter
    {
        if ($this->converter === null) {
            $this->converter = new HtmlConverter([
                'remove_nodes' => 'head style script img',
                'bold_style' => '',
                'italic_style' => '',
                'strip_tags' => true,
                'strip_placeholder_links' => true,
                'hard_break' => true,
            ]);

            $environment = $this->converter->getEnvironment();
            $environment->addConverter(new PlainTextConverter());
            $environment->addConverter(new BlockConverter());
            $environment->addConverter(new LinkConverter());
        }

        return $this->converter;
    }
}
