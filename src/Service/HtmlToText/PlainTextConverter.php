<?php

namespace Lle\HermesBundle\Service\HtmlToText;

use League\HTMLToMarkdown\Converter\ConverterInterface;
use League\HTMLToMarkdown\ElementInterface;

class PlainTextConverter implements ConverterInterface
{
    public function convert(ElementInterface $element): string
    {
        // Line breaks of preformatted text are kept
        if ($element->isDescendantOf('pre')) {
            return $element->getValue();
        }

        return (string)preg_replace('~\s+~u', ' ', $element->getValue());
    }

    /**
     * @return string[]
     */
    public function getSupportedTags(): array
    {
        return ['#text'];
    }
}
