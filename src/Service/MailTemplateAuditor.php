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

    /** Mails read at once: the table may hold a lot of mails in error. */
    public const MAIL_BATCH_SIZE = 200;

    /** Twig fields of a mail, as returned by `MailRepository::findContentsByStatus()`. */
    public const MAIL_FIELDS = ['subject', 'text', 'html'];

    public const MAIL_ID = 'id';
    public const CONTENT_HASH_ALGORITHM = 'xxh128';

    public const LINE_FORMAT = '%s (line %d)';
    public const TRANSLATION_FIELD_FORMAT = '%s (%s)';

    /**
     * Error of each content already checked, by hash: most mails reuse the content of their template,
     * which is then compiled once.
     *
     * @var array<string, ?string>
     */
    protected array $checkedContents = [];

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
        $this->checkedContents = [];
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

        return [...$issues, ...$this->auditMails()];
    }

    /**
     * Mails sharing the same refused content are reported once, with their number of occurrences.
     *
     * @return TemplateIssue[]
     */
    protected function auditMails(): array
    {
        /** @var array<string, TemplateIssue> $issues */
        $issues = [];
        $afterId = 0;

        do {
            $mails = $this->mailRepository->findContentsByStatus(
                self::MAIL_STATUSES_TO_CHECK,
                $afterId,
                self::MAIL_BATCH_SIZE,
            );

            foreach ($mails as $mail) {
                foreach (self::MAIL_FIELDS as $field) {
                    $content = $mail[$field];
                    $error = $this->check($content);
                    if ($error === null) {
                        continue;
                    }

                    $key = $field . $this->hash((string) $content);
                    if (isset($issues[$key])) {
                        $issues[$key]->addOccurrence();
                    } else {
                        $issues[$key] = new TemplateIssue(self::SOURCE_MAIL, $mail[self::MAIL_ID], $field, $error);
                    }
                }
                $afterId = $mail[self::MAIL_ID];
            }
        } while (count($mails) === self::MAIL_BATCH_SIZE);

        return array_values($issues);
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
            $error = $this->check($value);
            if ($error !== null) {
                $issues[] = new TemplateIssue($source, $id, $field, $error);
            }
        }

        return $issues;
    }

    /**
     * @return ?string the reason why the mail Twig environment refuses the content, null if it accepts it
     */
    protected function check(?string $content): ?string
    {
        if ($content === null || $content === '') {
            return null;
        }

        $hash = $this->hash($content);
        if (!array_key_exists($hash, $this->checkedContents)) {
            try {
                $this->twig->checkTemplate($content);
                $this->checkedContents[$hash] = null;
            } catch (TwigError $exception) {
                $this->checkedContents[$hash] = $this->describe($exception);
            }
        }

        return $this->checkedContents[$hash];
    }

    protected function hash(string $content): string
    {
        return hash(self::CONTENT_HASH_ALGORITHM, $content);
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
