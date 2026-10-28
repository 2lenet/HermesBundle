<?php

namespace App\Tests\Service;

use League\HTMLToMarkdown\HtmlConverter;
use Lle\HermesBundle\Service\HtmlToTextConverter;
use PHPUnit\Framework\TestCase;

class HtmlToTextConverterTest extends TestCase
{
    public function testLogoAndBoldAreRemovedAndLinksKept(): void
    {
        if (!class_exists(HtmlConverter::class)) {
            self::markTestSkipped('league/html-to-markdown is not installed');
        }

        $text = (new HtmlToTextConverter())->convert(
            '<html><head><style>p {}</style></head><body>'
            . '<p><a href="https://helyon.fr"><img src="https://helyon.fr/logo.png" alt="Helyon"></a></p>'
            . '<p><img src="https://helyon.fr/logo.png" alt="Helyon"></p>'
            . '<p>Bonjour <strong>Jean</strong>, voir <a href="https://helyon.fr/offre">l\'offre</a></p>'
            . '</body></html>',
            'utf-8'
        );

        self::assertStringNotContainsString('logo.png', $text);
        self::assertStringNotContainsString('](https://helyon.fr)', $text);
        self::assertStringNotContainsString('**', $text);
        self::assertStringNotContainsString('p {}', $text);
        self::assertStringStartsWith('Bonjour Jean, voir', $text);
        self::assertStringContainsString('(https://helyon.fr/offre)', $text);
    }

    public function testDefaultConverterWithoutLeague(): void
    {
        $text = (new HtmlToTextConverter())->convert('<p>Bonjour <strong>Jean</strong></p>', 'utf-8');

        self::assertStringContainsString('Bonjour Jean', $text);
    }
}
