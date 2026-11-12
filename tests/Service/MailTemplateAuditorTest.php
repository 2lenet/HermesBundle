<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Lle\HermesBundle\Contracts\TemplateRepositoryInterface;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Model\TemplateIssue;
use Lle\HermesBundle\Repository\MailRepository;
use Lle\HermesBundle\Service\MailTemplateAuditor;
use Lle\HermesBundle\Translatable\Translation\TemplateTranslation;
use Lle\HermesBundle\Translatable\TranslatableTemplate;
use Lle\HermesBundle\Twig\MailSecurityPolicyFactory;
use Lle\HermesBundle\Twig\MailTwigEnvironment;
use PHPUnit\Framework\TestCase;

class MailTemplateAuditorTest extends TestCase
{
    private const VALID = 'Booking {{ booking.reference|upper }}';
    private const FORBIDDEN_FUNCTION = "{{ include('base.html.twig') }}";
    private const FORBIDDEN_FUNCTION_ERROR = 'Function "include" is not allowed. (line 1)';
    private const FORBIDDEN_FILTER = "{{ ['a']|map(value => value)|join }}";
    private const FORBIDDEN_FILTER_ERROR = 'Filter "map" is not allowed. (line 1)';
    private const INVALID = '{% if booking %}unclosed';

    public function testReportsNothingWhenEveryTemplateIsAllowed(): void
    {
        $template = $this->createTemplate(1, self::VALID);
        $mail = $this->createMailRow(10, self::VALID);
        $auditor = $this->createAuditor([$template], [$mail]);

        $issues = $auditor->audit();

        self::assertSame([], $issues);
    }

    public function testReportsEachRefusedFieldOfTemplatesAndMails(): void
    {
        $template = $this->createTemplate(1, self::VALID);
        $template->setHtml(self::FORBIDDEN_FILTER);
        $mail = $this->createMailRow(10, self::FORBIDDEN_FUNCTION);
        $auditor = $this->createAuditor([$template], [$mail]);

        $issues = $auditor->audit();

        self::assertEquals([
            new TemplateIssue(MailTemplateAuditor::SOURCE_TEMPLATE, 1, 'html', self::FORBIDDEN_FILTER_ERROR),
            new TemplateIssue(MailTemplateAuditor::SOURCE_MAIL, 10, 'subject', self::FORBIDDEN_FUNCTION_ERROR),
        ], $issues);
    }

    public function testReportsSyntaxErrorsWithTheirLine(): void
    {
        $template = $this->createTemplate(1, self::INVALID);
        $auditor = $this->createAuditor([$template], []);

        $issues = $auditor->audit();

        self::assertCount(1, $issues);
        self::assertSame('subject', $issues[0]->getField());
        self::assertStringContainsString('(line 1)', $issues[0]->getError());
    }

    public function testOnlyChecksMailsThatMayStillBeSent(): void
    {
        $mailRepository = $this->createMock(MailRepository::class);
        $mailRepository->expects(self::once())
            ->method('findContentsByStatus')
            ->with(MailTemplateAuditor::MAIL_STATUSES_TO_CHECK, 0, MailTemplateAuditor::MAIL_BATCH_SIZE)
            ->willReturn([]);
        $auditor = new MailTemplateAuditor(
            $this->createTwig(),
            $this->createTemplateRepository([]),
            $mailRepository,
            $this->createMock(EntityManagerInterface::class),
            false,
        );

        $issues = $auditor->audit();

        self::assertSame([], $issues);
    }

    public function testReadsMailsByBatchesUntilTheLastOne(): void
    {
        $firstBatch = [];
        for ($id = 1; $id <= MailTemplateAuditor::MAIL_BATCH_SIZE; $id++) {
            $firstBatch[] = $this->createMailRow($id, self::VALID);
        }
        $lastBatch = [$this->createMailRow(MailTemplateAuditor::MAIL_BATCH_SIZE + 1, self::FORBIDDEN_FUNCTION)];
        $mailRepository = $this->createMock(MailRepository::class);
        $mailRepository->expects(self::exactly(2))
            ->method('findContentsByStatus')
            ->willReturnCallback(
                static fn (array $statuses, int $afterId): array => $afterId === 0 ? $firstBatch : $lastBatch,
            );
        $auditor = new MailTemplateAuditor(
            $this->createTwig(),
            $this->createTemplateRepository([]),
            $mailRepository,
            $this->createMock(EntityManagerInterface::class),
            false,
        );

        $issues = $auditor->audit();

        self::assertEquals([
            new TemplateIssue(
                MailTemplateAuditor::SOURCE_MAIL,
                MailTemplateAuditor::MAIL_BATCH_SIZE + 1,
                'subject',
                self::FORBIDDEN_FUNCTION_ERROR,
            ),
        ], $issues);
    }

    public function testGroupsMailsSharingTheSameRefusedContent(): void
    {
        $mails = [
            $this->createMailRow(10, self::FORBIDDEN_FUNCTION),
            $this->createMailRow(11, self::FORBIDDEN_FUNCTION),
            $this->createMailRow(12, self::FORBIDDEN_FILTER),
            $this->createMailRow(13, self::FORBIDDEN_FUNCTION),
        ];
        $auditor = $this->createAuditor([], $mails);

        $issues = $auditor->audit();

        self::assertEquals([
            new TemplateIssue(MailTemplateAuditor::SOURCE_MAIL, 10, 'subject', self::FORBIDDEN_FUNCTION_ERROR, 3),
            new TemplateIssue(MailTemplateAuditor::SOURCE_MAIL, 12, 'subject', self::FORBIDDEN_FILTER_ERROR),
        ], $issues);
    }

    public function testChecksTemplateTranslationsWhenMailsAreTranslatable(): void
    {
        $translatableTemplate = new TranslatableTemplate();
        $translatableTemplate->setId(3);
        $translation = new TemplateTranslation('en', 'html', self::FORBIDDEN_FILTER);
        $translation->setObject($translatableTemplate);
        $translationRepository = $this->createMock(EntityRepository::class);
        $translationRepository->method('findBy')
            ->with(['field' => MailTemplateAuditor::TEMPLATE_FIELDS])
            ->willReturn([$translation]);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')
            ->with(TemplateTranslation::class)
            ->willReturn($translationRepository);
        $auditor = new MailTemplateAuditor(
            $this->createTwig(),
            $this->createTemplateRepository([]),
            $this->createMailRepository([]),
            $entityManager,
            true,
        );

        $issues = $auditor->audit();

        self::assertEquals([
            new TemplateIssue(MailTemplateAuditor::SOURCE_TRANSLATION, 3, 'html (en)', self::FORBIDDEN_FILTER_ERROR),
        ], $issues);
    }

    /**
     * @param Template[]                                   $templates
     * @param array<int, array<string, int|string|null>> $mails
     */
    private function createAuditor(array $templates, array $mails): MailTemplateAuditor
    {
        return new MailTemplateAuditor(
            $this->createTwig(),
            $this->createTemplateRepository($templates),
            $this->createMailRepository($mails),
            $this->createMock(EntityManagerInterface::class),
            false,
        );
    }

    private function createTwig(): MailTwigEnvironment
    {
        return new MailTwigEnvironment([], MailSecurityPolicyFactory::create());
    }

    /**
     * @param Template[] $templates
     */
    private function createTemplateRepository(array $templates): TemplateRepositoryInterface
    {
        $templateRepository = $this->createMock(TemplateRepositoryInterface::class);
        $templateRepository->method('findAll')->willReturn($templates);

        return $templateRepository;
    }

    /**
     * @param array<int, array<string, int|string|null>> $mails
     */
    private function createMailRepository(array $mails): MailRepository
    {
        $mailRepository = $this->createMock(MailRepository::class);
        $mailRepository->method('findContentsByStatus')
            ->willReturnCallback(static fn (array $statuses, int $afterId): array => $afterId === 0 ? $mails : []);

        return $mailRepository;
    }

    /**
     * @return array{id: int, subject: string, text: null, html: null}
     */
    private function createMailRow(int $id, string $subject): array
    {
        return ['id' => $id, 'subject' => $subject, 'text' => null, 'html' => null];
    }

    private function createTemplate(int $id, string $subject): Template
    {
        $template = new Template();
        $template->setId($id);
        $template->setSubject($subject);

        return $template;
    }
}
