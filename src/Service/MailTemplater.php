<?php

namespace Lle\HermesBundle\Service;

use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Twig\MailTwigEnvironment;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

/**
 * This object allows us to generate mail contents from template & data.
 * Do not use injection for this class, instead create an instance.
 */
class MailTemplater
{
    public const PACKAGE = '2lenet/hermes-bundle';
    public const DEPRECATED_SINCE = '1.15';

    protected array $data = [];

    public function __construct(
        protected Mail $mail,
        protected Environment $twig,
        protected RouterInterface $router,
    ) {
        if (!$twig instanceof MailTwigEnvironment) {
            trigger_deprecation(
                self::PACKAGE,
                self::DEPRECATED_SINCE,
                'Passing a Twig environment other than "%s" to "%s" is deprecated: the template is not sandboxed. '
                . 'Use the "%s" service. The constructor will require it in 2.0.',
                MailTwigEnvironment::class,
                self::class,
                MailTwigEnvironment::class,
            );
        }
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
    protected function render(string $string, bool $decodeHtml = true): string
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
