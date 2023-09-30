<?php

namespace phpunit\Unit\Service\Factory;

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
        self::assertEquals(0, $recipient->getNbRetry());
        self::assertNull($recipient->getMail());
        self::assertNull($recipient->getOpenDate());
        self::assertNull($recipient->getCcMail());
        self::assertCount(0, $recipient->getLinkOpenings());
        self::assertFalse($recipient->isTest());
    }

    protected function createContactDto(): ContactDto
    {
        $contact = new ContactDto('to', 'to@email.com');
        $contact->setData(['data']);

        return $contact;
    }
}
