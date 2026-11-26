<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Tests\Twig;

use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Service\MailPreviewTemplater;
use Lle\HermesBundle\Twig\HermesExtension;
use Lle\HermesBundle\Twig\MailSecurityPolicyFactory;
use Lle\HermesBundle\Twig\MailTwigEnvironment;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class HermesExtensionTest extends TestCase
{
    private const FORBIDDEN_SUBJECT = "{{ include('base.html.twig') }}";
    private const FORBIDDEN_HTML = "<p>{{ ['a']|map(value => value)|join }}</p>";
    private const FORBIDDEN_FUNCTION_ERROR = MailPreviewTemplater::ERROR_PREFIX . 'Function "include" is not allowed.';
    private const FORBIDDEN_FILTER_ERROR = MailPreviewTemplater::ERROR_PREFIX . 'Filter "map" is not allowed.';

    public function testTemplaterRendersTheMailWithItsData(): void
    {
        $mail = $this->createMail('Booking {{ reference }}', '<p>{{ reference }}</p>');
        $extension = $this->createExtension();

        $templater = $extension->getTemplater($mail);

        self::assertSame('Booking ABC123', $templater->getSubject());
        self::assertSame('<p>ABC123</p>', $templater->getHtml());
    }

    public function testTemplaterShowsTheErrorOfATemplateRefusedBySandbox(): void
    {
        $mail = $this->createMail(self::FORBIDDEN_SUBJECT, self::FORBIDDEN_HTML);
        $extension = $this->createExtension();

        $templater = $extension->getTemplater($mail);

        self::assertSame(self::FORBIDDEN_FUNCTION_ERROR, $templater->getSubject());
        self::assertSame(self::FORBIDDEN_FILTER_ERROR, $templater->getHtml());
    }

    public function testTemplaterShowsTheErrorOfAnInvalidTemplate(): void
    {
        $mail = $this->createMail('{% if reference %}unclosed', '');
        $extension = $this->createExtension();

        $subject = $extension->getTemplater($mail)->getSubject();

        self::assertStringStartsWith(MailPreviewTemplater::ERROR_PREFIX, $subject);
    }

    private function createExtension(): HermesExtension
    {
        $twig = new MailTwigEnvironment([], MailSecurityPolicyFactory::create());

        return new HermesExtension($twig, $this->createMock(RouterInterface::class));
    }

    private function createMail(string $subject, string $html): Mail
    {
        $mail = new Mail();
        $mail->setTemplate(new Template());
        $mail->setData(['reference' => 'ABC123']);
        $mail->setSubject($subject);
        $mail->setHtml($html);

        return $mail;
    }
}
