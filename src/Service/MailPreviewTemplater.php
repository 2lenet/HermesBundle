<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Service;

use Twig\Error\Error as TwigError;

/**
 * Mail templater of the back office screens (dashboard, mail show): a template that cannot be
 * rendered — refused by the sandbox, invalid, failing on the mail data — shows its error instead
 * of breaking the page. Mails saved before the sandbox was enabled were never checked against it.
 *
 * Sending keeps using `MailTemplater`, whose errors put the recipient in error.
 */
class MailPreviewTemplater extends MailTemplater
{
    public const ERROR_PREFIX = Sender::TEMPLATE_ERROR_PREFIX;

    protected function render(string $string, bool $decodeHtml = true): string
    {
        try {
            return parent::render($string, $decodeHtml);
        } catch (TwigError $exception) {
            return self::ERROR_PREFIX . $exception->getRawMessage();
        }
    }
}
