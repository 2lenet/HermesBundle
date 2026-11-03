<?php

namespace Lle\HermesBundle\Service\HtmlToText;

use League\HTMLToMarkdown\Configuration;
use League\HTMLToMarkdown\ConfigurationAwareInterface;
use League\HTMLToMarkdown\Converter\ConverterInterface;
use League\HTMLToMarkdown\Converter\LinkConverter as LeagueLinkConverter;
use League\HTMLToMarkdown\ElementInterface;

class LinkConverter implements ConverterInterface, ConfigurationAwareInterface
{
    private LeagueLinkConverter $linkConverter;

    public function __construct()
    {
        $this->linkConverter = new LeagueLinkConverter();
    }

    public function setConfig(Configuration $config): void
    {
        $this->linkConverter->setConfig($config);
    }

    public function convert(ElementInterface $element): string
    {
        $text = trim($element->getValue());
        if ($text === '') {
            return '';
        }

        // A link showing its URL is rendered as the URL, instead of <url>
        $href = $element->getAttribute('href');
        if ($href === $text || $href === 'mailto:' . $text) {
            return $text;
        }

        return $this->linkConverter->convert($element);
    }

    /**
     * @return string[]
     */
    public function getSupportedTags(): array
    {
        return ['a'];
    }
}
