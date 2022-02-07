<?php

namespace Lle\HermesBundle\Twig;

use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Service\MailTemplater;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class HermesExtension extends AbstractExtension
{
    private Environment $twig;

    private RouterInterface $router;

    public function __construct(Environment $twig, RouterInterface $router)
    {
        $this->twig = $twig;
        $this->router = $router;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction("lle_hermes_get_templater", [$this, "getTemplater"]),
        ];
    }

    public function getTemplater(Mail $mail): MailTemplater
    {
        $templater = new MailTemplater($mail, $this->twig, $this->router);
        $templater->addData($mail->getData());

        return $templater;
    }
}
