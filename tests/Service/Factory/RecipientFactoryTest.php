<?php

namespace App\Tests\Service\Factory;

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

    protected function createContactDto(): ContactDto
    {
        $contact = new ContactDto('to', 'to@email.com');
        $contact->setData(['data']);

        return $contact;
    }
}
