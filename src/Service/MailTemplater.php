<?php

namespace Lle\HermesBundle\Service;

use Lle\HermesBundle\Entity\Mail;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

/**
 * This object allows us to generate mail contents from template & data.
 * Do not use injection for this class, instead create an instance.
 */
class MailTemplater
{
    protected Mail $mail;

    protected Environment $twig;

    protected RouterInterface $router;

    protected array $data = [];

    public function __construct(Mail $mail, Environment $twig, RouterInterface $router)
    {
        $this->mail = $mail;
        $this->twig = $twig;
        $this->router = $router;
    }

    public function getSubject(): string
    {
        $this->twig->disableStrictVariables();

        $result = $this->twig
            ->createTemplate($this->mail->getSubject())
            ->render($this->data);

        if ($this->twig->isDebug()) {
            $this->twig->enableStrictVariables();
        }

        return $result;
    }

    public function getText(): string
    {
        $this->twig->disableStrictVariables();

        $result = $this->twig
            ->createTemplate((string)$this->mail->getText())
            ->render($this->data);

        if ($this->twig->isDebug()) {
            $this->twig->enableStrictVariables();
        }

        return $result;
    }

    public function getHtml(): string
    {
        $this->twig->disableStrictVariables();

        $result = $this->twig
            ->createTemplate((string)$this->mail->getHtml())
            ->render($this->data);

        if ($this->twig->isDebug()) {
            $this->twig->enableStrictVariables();
        }

        return $result;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function addData(array $add): void
    {
        $this->data = array_merge($this->data, $add);
    }
}
