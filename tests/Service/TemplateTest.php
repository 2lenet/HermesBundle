<?php

namespace Service;

use Lle\HermesBundle\Entity\Template;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Loader\LoaderInterface;

/**
 * Class MTemplateTest
 * @package phpunit\Unit\Service
 *
 * @author 2LE <2le@2le.net>
 */
class TemplateTest extends TestCase
{
    private Template $template;
    public function setUp(): void
    {
        parent::setUp();

        $this->template = $this->createTemplate();
    }

    public function createTemplate(): Template
    {
        $template = new Template();
        $template->setSubject('subject');
        $template->setText('text');
        $template->setHtml('<p>html</p>');
        $template->setSenderName('senderName');
        $template->setSenderEmail('senderEmail');
        $template->setMjml('<mjml>mjml</mjml>');

        return $template;
    }
}
