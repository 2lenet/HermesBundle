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
    protected array $data = [];

    public function __construct(
        protected Mail $mail,
        protected Environment $twig,
        protected RouterInterface $router,
    ) {
    }

    public function getSubject(): string
    {
        return $this->render((string)$this->mail->getSubject());
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
        return $this->render((string)$this->mail->getTemplate()?->getSenderName());
    }

    /**
     * An unknown variable renders as an empty string. When the given environment is strict (an
     * application environment rather than `MailTwigEnvironment`), strictness is lifted for the
     * rendering only and restored afterwards, even if the rendering fails.
     */
    private function render(string $string, bool $decodeHtml = true): string
    {
        $wasStrict = $this->twig->isStrictVariables();
        if ($wasStrict) {
            $this->twig->disableStrictVariables();
        }

        try {
            $result = $this->twig
                ->createTemplate($string)
                ->render($this->data);
        } finally {
            if ($wasStrict) {
                $this->twig->enableStrictVariables();
            }
        }

        return $decodeHtml ? html_entity_decode($result) : $result;
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
