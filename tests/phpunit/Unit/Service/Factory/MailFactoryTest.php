<?php

namespace phpunit\Unit\Service\Factory;

use Lle\HermesBundle\Contracts\MultiTenantInterface;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Entity\Template;
use Lle\HermesBundle\Model\ContactDto;
use Lle\HermesBundle\Model\MailDto;
use Lle\HermesBundle\Service\Factory\MailFactory;
use Lle\HermesBundle\Service\Factory\RecipientFactory;
use Lle\HermesBundle\Service\MultiTenantManager;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Class MailFactoryTest
 * @package phpunit\Unit\Service\Factory
 *
 * @author 2LE <2le@2le.net>
 */
class MailFactoryTest extends TestCase
{
    private MailFactory $mailFactory;

    public function setUp(): void
    {
        parent::setUp();

        $multiTenantManager = $this->createMock(MultiTenantManager::class);
        $multiTenantManager->method('isMultiTenantEnabled')->willReturn(true);
        $parameters = new ParameterBag();
        $security = $this->createMock(Security::class);
        $recipientFactory = new RecipientFactory();
        $this->mailFactory = new MailFactory($multiTenantManager, $parameters, $recipientFactory, $security);
    }

    public function testCreateMailFromDto(): void
    {
        $mailDto = $this->createMailDto();
        $template = $this->createTemplate();

        $mail = $this->mailFactory->createMailFromDto($mailDto, $template);

        self::assertInstanceOf(Mail::class, $mail);

        self::assertInstanceOf(Template::class, $mail->getTemplate());
        self::assertEquals(1, $mail->getTemplate()->getId());
        self::assertEquals('label', $mail->getTemplate()->getLibelle());
        self::assertEquals('subject', $mail->getTemplate()->getSubject());
        self::assertEquals('no-reply@email.com', $mail->getTemplate()->getSenderEmail());
        self::assertEquals('text', $mail->getTemplate()->getText());
        self::assertEquals('code', $mail->getTemplate()->getCode());
        self::assertEquals('html', $mail->getTemplate()->getHtml());
        self::assertTrue($mail->getTemplate()->isUnsubscriptions());
        self::assertTrue($mail->getTemplate()->hasStatistics());

        self::assertEquals(['data'], $mail->getData());
        self::assertEquals(4, $mail->getTotalToSend());
        self::assertEquals(0, $mail->getTotalSended());

        self::assertCount(2, $mail->getRecipients());
        self::assertInstanceOf(Recipient::class, $mail->getRecipients()->first());
        self::assertEquals('to1@email.com', $mail->getRecipients()->first()->getToEmail());
        self::assertEquals('to1', $mail->getRecipients()->first()->getToName());
        self::assertInstanceOf(Recipient::class, $mail->getRecipients()->last());
        self::assertEquals('to2@email.com', $mail->getRecipients()->last()->getToEmail());
        self::assertEquals('to2', $mail->getRecipients()->last()->getToName());

        self::assertEquals('subject', $mail->getSubject());
        self::assertNull($mail->getSendingDate());
        self::assertNull($mail->getText());
        self::assertEquals('html', $mail->getHtml());
        self::assertEquals(0, $mail->getTotalUnsubscribed());
        self::assertEquals(0, $mail->getTotalError());
        self::assertEquals([], $mail->getAttachement());
        self::assertEquals(0, $mail->getTotalOpened());

        self::assertCount(2, $mail->getCcRecipients());
        self::assertInstanceOf(Recipient::class, $mail->getRecipients()->first());
        self::assertEquals('cc1@email.com', $mail->getCcRecipients()->first()->getToEmail());
        self::assertEquals('cc1', $mail->getCcRecipients()->first()->getToName());
        self::assertInstanceOf(Recipient::class, $mail->getRecipients()->last());
        self::assertEquals('cc2@email.com', $mail->getCcRecipients()->last()->getToEmail());
        self::assertEquals('cc2', $mail->getCcRecipients()->last()->getToName());
    }

    public function testCreateMailFromDtoWithTenantId(): void
    {
        $mailDto = $this->createMailDto(1);
        $template = $this->createTemplate();

        $mail = $this->mailFactory->createMailFromDto($mailDto, $template);

        self::assertInstanceOf(Mail::class, $mail);

        self::assertInstanceOf(Template::class, $mail->getTemplate());
        self::assertEquals(1, $mail->getTemplate()->getId());
        self::assertEquals('label', $mail->getTemplate()->getLibelle());
        self::assertEquals('subject', $mail->getTemplate()->getSubject());
        self::assertEquals('no-reply@email.com', $mail->getTemplate()->getSenderEmail());
        self::assertEquals('text', $mail->getTemplate()->getText());
        self::assertEquals('code', $mail->getTemplate()->getCode());
        self::assertEquals('html', $mail->getTemplate()->getHtml());
        self::assertTrue($mail->getTemplate()->isUnsubscriptions());
        self::assertTrue($mail->getTemplate()->hasStatistics());

        self::assertEquals(['data'], $mail->getData());
        self::assertEquals(4, $mail->getTotalToSend());
        self::assertEquals(0, $mail->getTotalSended());

        self::assertCount(2, $mail->getRecipients());
        self::assertInstanceOf(Recipient::class, $mail->getRecipients()->first());
        self::assertEquals('to1@email.com', $mail->getRecipients()->first()->getToEmail());
        self::assertEquals('to1', $mail->getRecipients()->first()->getToName());
        self::assertInstanceOf(Recipient::class, $mail->getRecipients()->last());
        self::assertEquals('to2@email.com', $mail->getRecipients()->last()->getToEmail());
        self::assertEquals('to2', $mail->getRecipients()->last()->getToName());
        self::assertEquals(1, $mail->getRecipients()->last()->getTenantId());

        self::assertEquals('subject', $mail->getSubject());
        self::assertNull($mail->getSendingDate());
        self::assertNull($mail->getText());
        self::assertEquals('html', $mail->getHtml());
        self::assertEquals(0, $mail->getTotalUnsubscribed());
        self::assertEquals(0, $mail->getTotalError());
        self::assertEquals([], $mail->getAttachement());
        self::assertEquals(0, $mail->getTotalOpened());
        self::assertEquals(1, $mail->getTenantId());

        self::assertCount(2, $mail->getCcRecipients());
        self::assertInstanceOf(Recipient::class, $mail->getRecipients()->first());
        self::assertEquals('cc1@email.com', $mail->getCcRecipients()->first()->getToEmail());
        self::assertEquals('cc1', $mail->getCcRecipients()->first()->getToName());
        self::assertInstanceOf(Recipient::class, $mail->getRecipients()->last());
        self::assertEquals('cc2@email.com', $mail->getCcRecipients()->last()->getToEmail());
        self::assertEquals('cc2', $mail->getCcRecipients()->last()->getToName());
        self::assertEquals(1, $mail->getCcRecipients()->last()->getTenantId());
    }

    protected function createMailDto(?int $tenantId = null): MailDto
    {
        $mail = new MailDto();
        $mail->setSubject('subject');
        $mail->addTo(new ContactDto('to1', 'to1@email.com'));
        $mail->addTo(new ContactDto('to2', 'to2@email.com'));
        $mail->addCc(new ContactDto('cc1', 'cc1@email.com'));
        $mail->addCc(new ContactDto('cc2', 'cc2@email.com'));
        $mail->setFrom(new ContactDto('from', 'from@email.com'));
        $mail->setTemplate('code');
        $mail->setStatus(MailDto::DRAFT);
        $mail->setData(['data']);
        $mail->setSendHtml(true);
        $mail->setSendText(false);
        $mail->setTenantId($tenantId);

        return $mail;
    }

    protected function createTemplate(): Template
    {
        $template = new Template();
        $template->setId(1);
        $template->setLibelle('label');
        $template->setSubject('subject');
        $template->setSenderEmail('no-reply@email.com');
        $template->setText('text');
        $template->setCode('code');
        $template->setHtml('html');
        $template->setUnsubscriptions(true);
        $template->setStatistics(true);

        return $template;
    }
}
