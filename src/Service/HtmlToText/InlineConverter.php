<?php

namespace Lle\HermesBundle\Service\HtmlToText;

use League\HTMLToMarkdown\Converter\ConverterInterface;
use League\HTMLToMarkdown\ElementInterface;

/**
 * Inline elements rendered as their text, without markdown markers (backticks...).
 */
class InlineConverter implements ConverterInterface
{
    public function convert(ElementInterface $element): string
    {
        return $element->getValue();
    }

    /**
     * @return string[]
     */
    public function getSupportedTags(): array
    {
        return ['code'];
    }
}
