<?php

namespace App\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Consent;
use Lle\HermesBundle\Repository\ConsentRepository;
use Lle\HermesBundle\Service\ConsentManager;
use PHPUnit\Framework\TestCase;

class ConsentManagerTest extends TestCase
{
    public function testHasConsentReturnsTrueWhenNoConsentStored(): void
    {
        $repository = $this->createMock(ConsentRepository::class);
        $repository->method('findOneBy')->willReturn(null);

        $manager = new ConsentManager($this->createMock(EntityManagerInterface::class), $repository);

        $this->assertTrue($manager->hasConsent('recipient@2le.net', Consent::TYPE_TRACKING));
    }

    public function testHasConsentReturnsStoredValue(): void
    {
        $consent = new Consent();
        $consent->setValue(false);

        $repository = $this->createMock(ConsentRepository::class);
        $repository->method('findOneBy')->willReturn($consent);

        $manager = new ConsentManager($this->createMock(EntityManagerInterface::class), $repository);

        $this->assertFalse($manager->hasConsent('recipient@2le.net', Consent::TYPE_TRACKING));
    }

    public function testSetConsentCreatesConsentWhenNoneExists(): void
    {
        $repository = $this->createMock(ConsentRepository::class);
        $repository->method('findOneBy')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $manager = new ConsentManager($em, $repository);

        $consent = $manager->setConsent('recipient@2le.net', Consent::TYPE_TRACKING, false);

        $this->assertSame('recipient@2le.net', $consent->getEmail());
        $this->assertSame(Consent::TYPE_TRACKING, $consent->getType());
        $this->assertFalse($consent->isValue());
    }

    public function testSetConsentUpdatesExistingConsent(): void
    {
        $consent = new Consent();
        $consent->setEmail('recipient@2le.net');
        $consent->setType(Consent::TYPE_TRACKING);
        $consent->setValue(true);

        $repository = $this->createMock(ConsentRepository::class);
        $repository->method('findOneBy')->willReturn($consent);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('persist');
        $em->expects($this->once())->method('flush');

        $manager = new ConsentManager($em, $repository);

        $result = $manager->setConsent('recipient@2le.net', Consent::TYPE_TRACKING, false);

        $this->assertSame($consent, $result);
        $this->assertFalse($result->isValue());
    }
}
