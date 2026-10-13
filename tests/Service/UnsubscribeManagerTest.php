<?php

namespace App\Tests\Service;

use Lle\HermesBundle\Entity\TypeTemplate;
use Lle\HermesBundle\Repository\UnsubscribeEmailRepository;
use Lle\HermesBundle\Service\UnsubscribeManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class UnsubscribeManagerTest extends TestCase
{
    public function testTokenWithoutTypeIsTheOneOfOldLinks(): void
    {
        $manager = $this->getManager();

        $this->assertSame(md5('recipient@2le.net' . 'secret'), $manager->getToken('recipient@2le.net'));
    }

    public function testTokenIsOnlyValidForItsType(): void
    {
        $commercial = (new TypeTemplate())->setCode('COMMERCIAL');
        $news = (new TypeTemplate())->setCode('NEWS');

        $manager = $this->getManager();
        $token = $manager->getToken('recipient@2le.net', $commercial);

        $this->assertTrue($manager->isTokenValid('recipient@2le.net', $token, $commercial));
        $this->assertFalse($manager->isTokenValid('recipient@2le.net', $token, $news));
        $this->assertFalse($manager->isTokenValid('recipient@2le.net', $token));
    }

    public function testCreateUnsubscriptionLowercasesEmail(): void
    {
        $typeTemplate = (new TypeTemplate())->setCode('COMMERCIAL');

        $unsubscription = $this->getManager()->createUnsubscription('Recipient@2LE.net', $typeTemplate);

        $this->assertSame('recipient@2le.net', $unsubscription->getEmail());
        $this->assertSame($typeTemplate, $unsubscription->getTypeTemplate());
    }

    protected function getManager(): UnsubscribeManager
    {
        return new UnsubscribeManager(
            $this->createMock(UnsubscribeEmailRepository::class),
            new ParameterBag(['lle_hermes.app_secret' => 'secret']),
        );
    }
}
