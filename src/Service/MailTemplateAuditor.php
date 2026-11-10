<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Contracts\TemplateInterface;
use Lle\HermesBundle\Contracts\TemplateRepositoryInterface;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Model\TemplateIssue;
use Lle\HermesBundle\Repository\MailRepository;
use Lle\HermesBundle\Translatable\Translation\TemplateTranslation;
use Lle\HermesBundle\Twig\MailTwigEnvironment;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Error\Error as TwigError;

/**
 * Checks the templates and the mails stored in database against the mail Twig environment, without
 * rendering them: what was saved before the sandbox was enabled was never validated against it.
 */
class MailTemplateAuditor
{
    public const SOURCE_TEMPLATE = 'template';
    public const SOURCE_TRANSLATION = 'template translation';
    public const SOURCE_MAIL = 'mail';

    /** Twig fields of a template, as named in `TemplateTranslation::$field`. */
    public const TEMPLATE_FIELDS = ['subject', 'senderName', 'text', 'html'];

    /** Mails that may still be rendered by a sending or a resending. */
    public const MAIL_STATUSES_TO_CHECK = [
        Mail::STATUS_DRAFT,
        Mail::STATUS_SENDING,
        Mail::STATUS_ERROR,
        Mail::STATUS_PARTIALLY_SENT,
    ];

    public const LINE_FORMAT = '%s (line %d)';
    public const TRANSLATION_FIELD_FORMAT = '%s (%s)';

    public function __construct(
        protected MailTwigEnvironment $twig,
        protected TemplateRepositoryInterface $templateRepository,
        protected MailRepository $mailRepository,
        protected EntityManagerInterface $entityManager,
        #[Autowire('%lle_hermes.translatable_mail%')]
        protected bool $translatableMail,
    ) {
    }

    /**
     * @return TemplateIssue[]
     */
    public function audit(): array
    {
        $issues = [];

        /** @var TemplateInterface $template */
        foreach ($this->templateRepository->findAll() as $template) {
            $fields = [
                'subject' => $template->getSubject(),
                'senderName' => $template->getSenderName(),
                'text' => $template->getText(),
                'html' => $template->getHtml(),
            ];
            $issues = [...$issues, ...$this->checkFields(self::SOURCE_TEMPLATE, $template->getId(), $fields)];
        }

        if ($this->translatableMail) {
            /** @var TemplateTranslation[] $translations */
            $translations = $this->entityManager->getRepository(TemplateTranslation::class)
                ->findBy(['field' => self::TEMPLATE_FIELDS]);
            foreach ($translations as $translation) {
                $template = $translation->getObject();
                $templateId = $template instanceof TemplateInterface ? $template->getId() : null;
                $field = sprintf(self::TRANSLATION_FIELD_FORMAT, $translation->getField(), $translation->getLocale());
                $issues = [...$issues, ...$this->checkFields(
                    self::SOURCE_TRANSLATION,
                    $templateId,
                    [$field => $translation->getContent()],
                )];
            }
        }

        foreach ($this->mailRepository->findBy(['status' => self::MAIL_STATUSES_TO_CHECK]) as $mail) {
            $fields = [
                'subject' => $mail->getSubject(),
                'text' => $mail->getText(),
                'html' => $mail->getHtml(),
            ];
            $issues = [...$issues, ...$this->checkFields(self::SOURCE_MAIL, $mail->getId(), $fields)];
        }

        return $issues;
    }

    /**
     * @param array<string, ?string> $fields
     *
     * @return TemplateIssue[]
     */
    protected function checkFields(string $source, ?int $id, array $fields): array
    {
        $issues = [];
        foreach ($fields as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            try {
                $this->twig->checkTemplate($value);
            } catch (TwigError $exception) {
                $issues[] = new TemplateIssue($source, $id, $field, $this->describe($exception));
            }
        }

        return $issues;
    }

    protected function describe(TwigError $exception): string
    {
        $line = $exception->getTemplateLine();
        if ($line > 0) {
            return sprintf(self::LINE_FORMAT, $exception->getRawMessage(), $line);
        }

        return $exception->getRawMessage();
    }
}
