<?php

namespace App\Tests\Service\Factory;

use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Lle\HermesBundle\Model\ContactDto;
use Lle\HermesBundle\Service\Factory\RecipientFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class RecipientFactoryTest
 * @package phpunit\Unit\Service\Factory
 *
 * @author 2LE <2le@2le.net>
 */
class RecipientFactoryTest extends TestCase
{
    private RecipientFactory $recipientFactory;

    public function setUp(): void
    {
        parent::setUp();

        $this->recipientFactory = new RecipientFactory();
    }

    public function testCreateRecipientFromDto(): void
    {
        $contactDto = $this->createContactDto();

        $recipient = $this->recipientFactory->createRecipientFromDto($contactDto);

        self::assertInstanceOf(Recipient::class, $recipient);

        self::assertEquals('to@email.com', $recipient->getToEmail());
        self::assertEquals('to', $recipient->getToName());
        self::assertEquals(['data'], $recipient->getData());
        self::assertEquals(Recipient::STATUS_SENDING, $recipient->getStatus());
        self::assertNull($recipient->getMail());
        self::assertNull($recipient->getOpenDate());
        self::assertNull($recipient->getCcMail());
        self::assertCount(0, $recipient->getLinkOpenings());
        self::assertFalse($recipient->isTest());
    }

    public function testCreateRecipientFromDtoWithTenantId(): void
    {
        $contactDto = $this->createContactDto();

        $recipient = $this->recipientFactory->createRecipientFromDto($contactDto, 1);

        self::assertInstanceOf(Recipient::class, $recipient);

        self::assertEquals('to@email.com', $recipient->getToEmail());
        self::assertEquals('to', $recipient->getToName());
        self::assertEquals(['data'], $recipient->getData());
        self::assertEquals(Recipient::STATUS_SENDING, $recipient->getStatus());
        self::assertNull($recipient->getMail());
        self::assertNull($recipient->getOpenDate());
        self::assertNull($recipient->getCcMail());
        self::assertCount(0, $recipient->getLinkOpenings());
        self::assertFalse($recipient->isTest());
        self::assertEquals(1, $recipient->getTenantId());
    }

    public function testCopy(): void
    {
        $recipient = $this->createRecipient();

        $copy = $this->recipientFactory->copy($recipient);

        self::assertInstanceOf(Recipient::class, $copy);

        self::assertEquals('to@email.com', $copy->getToEmail());
        self::assertEquals('to', $copy->getToName());
        self::assertEquals(['data'], $copy->getData());
        self::assertEquals(Recipient::STATUS_SENDING, $copy->getStatus());
        self::assertNotNull($copy->getMail());
        self::assertNull($copy->getOpenDate());
        self::assertNull($copy->getCcMail());
        self::assertCount(0, $copy->getLinkOpenings());
        self::assertFalse($copy->isTest());
        self::assertNull($copy->getTenantId());
    }

    protected function createContactDto(): ContactDto
    {
        $contact = new ContactDto('to', 'to@email.com');
        $contact->setData(['data']);

        return $contact;
    }

    protected function createRecipient(): Recipient
    {
        $mail = new Mail();

        $recipient = new Recipient();
        $recipient
            ->setToName('to')
            ->setToEmail('to@email.com')
            ->setData(['data'])
            ->setStatus(Recipient::STATUS_SENT)
            ->setMail($mail)
            ->setOpenDate(new \DateTime('2025-10-01'))
            ->setErrorMessage('error');

        return $recipient;
    }
}
