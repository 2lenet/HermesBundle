<?php

namespace phpunit\Unit\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Repository\MailRepository;
use Lle\HermesBundle\Repository\RecipientRepository;
use Lle\HermesBundle\Repository\UnsubscribeEmailRepository;
use Lle\HermesBundle\Service\SenderService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

/**
 * Class SenderServiceTest
 * @package phpunit\Unit\Service
 *
 * @author 2LE <2le@2le.net>
 */
class SenderServiceTest extends TestCase
{

    public function testSendAllMail(): void
    {
        $sender = new SenderService(
            $this->getMockMailer(),
            $this->getMockEntityManager(),
            $this->getMockRecipientRepository(),
            $this->getMockUnsubscribeEmail(),
            $this->getMockMailRepository(),
            $this->getMockParameterBag(),
            $this->getMockRouter(),
            $this->getEnvironment()
        );
        $sender->sendAllMail();
    }

    protected function getMockMailer(): MailerInterface
    {
        return $this->createMock(MailerInterface::class);
    }

    protected function getMockEntityManager(): EntityManagerInterface
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        return $entityManager;
    }

    protected function getMockRecipientRepository(): RecipientRepository
    {
        $repo = $this->createMock(RecipientRepository::class);
        $repo->expects(self::exactly(1))->method('disableErrors');
        $repo->expects(self::exactly(1))
            ->method('findRecipientsSending')
            ->with(self::equalTo('ok'), self::equalTo('sending'), self::equalTo(10))
            ->willReturn([]);

        return $repo;
    }

    protected function getMockUnsubscribeEmail(): UnsubscribeEmailRepository
    {
        $repo = $this->createMock(UnsubscribeEmailRepository::class);
        $repo->expects(self::exactly(1))
            ->method('findEmailUnsubscribed')
            ->willReturn(['unsubscribe@email.com']);
        return $repo;
    }

    protected function getMockMailRepository(): MailRepository
    {
        $repo = $this->getMockBuilder(MailRepository::class)
            ->disableOriginalConstructor()
            ->addMethods(['findByStatus'])
            ->getMock();
        $repo->expects(self::exactly(1))
            ->method('findByStatus')
            ->with(self::equalTo('sending'))
            ->willReturn([]);
        return $repo;
    }

    protected function getMockParameterBag(): ParameterBagInterface
    {
        return $this->createMock(ParameterBagInterface::class);
    }

    protected function getMockRouter(): RouterInterface
    {
        return $this->createMock(RouterInterface::class);
    }

    protected function getEnvironment(): Environment
    {
        return $this->createMock(Environment::class);
    }
}
