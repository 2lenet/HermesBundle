<?php

namespace App\Tests\Service;

use Lle\HermesBundle\Service\HtmlToTextConverter;
use PHPUnit\Framework\TestCase;

class HtmlToTextConverterTest extends TestCase
{
    public function testLogoAndBoldAreRemovedAndLinksKept(): void
    {
        $text = (new HtmlToTextConverter())->convert(
            '<html><head><style>p {}</style></head><body>'
            . '<p><a href="https://helyon.fr"><img src="https://helyon.fr/logo.png" alt="Helyon"></a></p>'
            . '<p><img src="https://helyon.fr/logo.png" alt="Helyon"></p>'
            . '<p>Bonjour <strong>Jean</strong>, voir <a href="https://helyon.fr/offre">l\'offre</a></p>'
            . '</body></html>',
            'utf-8'
        );

        self::assertSame("Bonjour Jean, voir [l'offre](https://helyon.fr/offre)", $text);
    }

    public function testSpecialCharactersAreNotEscaped(): void
    {
        $text = (new HtmlToTextConverter())->convert(
            '<p>Commande REF_2024 de Dupont &amp; Fils, contact jean_dupont@mail.fr</p>'
            . '<p>Total : 5 * 3 [note] a &lt; b</p>'
            . '<p># 1</p>'
            . '<p>1. Pièces</p>'
            . '<p>- 10 %</p>',
            'utf-8'
        );

        self::assertSame(
            "Commande REF_2024 de Dupont & Fils, contact jean_dupont@mail.fr\n\n"
            . "Total : 5 * 3 [note] a < b\n\n"
            . "# 1\n\n"
            . "1. Pièces\n\n"
            . '- 10 %',
            $text
        );
    }

    public function testLayoutTableCellsAreSeparated(): void
    {
        $text = (new HtmlToTextConverter())->convert(
            '<html><body>
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center">
                        <table width="600">
                            <tr>
                                <td><a href="https://helyon.fr"><img src="logo.png" alt="Helyon"></a></td>
                            </tr>
                            <tr>
                                <td>
                                    <p>Bonjour Jean,</p>
                                    <p>Votre dossier est prêt.</p>
                                </td>
                            </tr>
                            <tr>
                                <td>Helyon</td>
                                <td>contact@helyon.fr</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            </body></html>',
            'utf-8'
        );

        self::assertSame(
            "Bonjour Jean,\n\nVotre dossier est prêt.\n\nHelyon\n\ncontact@helyon.fr",
            $text
        );
    }

    public function testLinkWithParenthesesInUrl(): void
    {
        $text = (new HtmlToTextConverter())->convert(
            '<p><a href="https://helyon.fr/page_(promo)"><img src="logo.png"></a></p>'
            . '<p>Voir <a href="https://helyon.fr/page_(promo)">la promo</a></p>',
            'utf-8'
        );

        self::assertSame('Voir [la promo](https://helyon.fr/page_(promo))', $text);
    }

    public function testConverterIsReused(): void
    {
        $converter = new HtmlToTextConverter();
        $html = '<ul><li>Un</li><li>Deux</li></ul><p>Bonjour <a href="https://helyon.fr/a">Jean</a></p>';

        $first = $converter->convert($html, 'utf-8');

        self::assertSame($first, $converter->convert($html, 'utf-8'));
        self::assertStringContainsString('[Jean](https://helyon.fr/a)', $first);
    }
}
