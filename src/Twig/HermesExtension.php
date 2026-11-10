<?php

namespace Lle\HermesBundle\Twig;

use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Service\MailPreviewTemplater;
use Lle\HermesBundle\Service\MailTemplater;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class HermesExtension extends AbstractExtension
{
    public function __construct(
        protected readonly Environment $twig,
        protected readonly RouterInterface $router,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction("lle_hermes_get_templater", $this->getTemplater(...)),
        ];
    }

    /**
     * Templater of the back office screens: a template that cannot be rendered shows its error
     * instead of breaking the page (see `MailPreviewTemplater`).
     */
    public function getTemplater(Mail $mail): MailTemplater
    {
        $templater = new MailPreviewTemplater($mail, $this->twig, $this->router);
        $templater->addData($mail->getData());

        return $templater;
    }
}
