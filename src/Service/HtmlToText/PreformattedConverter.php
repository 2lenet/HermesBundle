<?php

namespace Lle\HermesBundle\Service\HtmlToText;

use League\HTMLToMarkdown\Converter\ConverterInterface;
use League\HTMLToMarkdown\ElementInterface;

/**
 * Preformatted text rendered as a paragraph keeping its line breaks, without a ``` block.
 */
class PreformattedConverter implements ConverterInterface
{
    public function convert(ElementInterface $element): string
    {
        $value = trim($element->getValue(), "\r\n");

        return trim($value) !== '' ? $value . "\n\n" : '';
    }

    /**
     * @return string[]
     */
    public function getSupportedTags(): array
    {
        return ['pre'];
    }
}
