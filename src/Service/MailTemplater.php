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
        return $this->render($this->mail->getSubject());
    }

    public function getText(): string
    {
        return $this->render((string)$this->mail->getText());
    }

    public function getHtml(): string
    {
        return $this->render((string)$this->mail->getHtml(), false);
    }

    public function getSenderName(): string
    {
        return $this->render((string)$this->mail->getTemplate()->getSenderName());
    }

    private function render(string $string, bool $decodeHtml = true)
    {
        $this->twig->disableStrictVariables();

        $result = $this->twig
            ->createTemplate($string)
            ->render($this->data);

        if ($decodeHtml) {
            $result = html_entity_decode($result);
        }

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
