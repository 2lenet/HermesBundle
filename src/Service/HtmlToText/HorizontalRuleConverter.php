<?php

namespace Lle\HermesBundle\Service\HtmlToText;

use League\HTMLToMarkdown\Converter\ConverterInterface;
use League\HTMLToMarkdown\ElementInterface;

/**
 * Horizontal rules only separate blocks, without a --- line.
 */
class HorizontalRuleConverter implements ConverterInterface
{
    public function convert(ElementInterface $element): string
    {
        return "\n\n";
    }

    /**
     * @return string[]
     */
    public function getSupportedTags(): array
    {
        return ['hr'];
    }
}
