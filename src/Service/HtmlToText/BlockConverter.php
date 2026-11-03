<?php

namespace Lle\HermesBundle\Service\HtmlToText;

use League\HTMLToMarkdown\Converter\ConverterInterface;
use League\HTMLToMarkdown\ElementInterface;

class BlockConverter implements ConverterInterface
{
    public function convert(ElementInterface $element): string
    {
        $value = trim($element->getValue());

        return $value !== '' ? $value . "\n\n" : '';
    }

    /**
     * @return string[]
     */
    public function getSupportedTags(): array
    {
        return [
            'p', 'blockquote', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th',
        ];
    }
}
