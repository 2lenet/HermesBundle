<?php

namespace Lle\HermesBundle\Service\HtmlToText;

use League\HTMLToMarkdown\Converter\ConverterInterface;
use League\HTMLToMarkdown\ElementInterface;

class PlainTextConverter implements ConverterInterface
{
    public function convert(ElementInterface $element): string
    {
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
