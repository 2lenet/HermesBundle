<?php

namespace phpunit\Unit\DependencyInjection;

use Lle\HermesBundle\DependencyInjection\LleHermesExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class LleHermesExtensionTest
 * @package phpunit\Unit\DependencyInjection
 *
 * @author 2LE <2le@2le.net>
 */
class LleHermesExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $extension = new LleHermesExtension();
        $containerBuilder = $this->getMockContainerBuilder();
        $extension->load(
            [
                'lle_hermes' => [
                    'root_dir' => '',
                    'upload_path' => '',
                    'app_secret' => '',
                    'app_domain' => '',
                    'bounce_email' => '',
                    'bounce_pass' => '',
                    'bounce_host' => '',
                    'menu_icons' => '',
                ],
            ],
            $containerBuilder
        );
    }

    protected function getMockContainerBuilder(): ContainerBuilder
    {
        $mock = $this->getMockBuilder(ContainerBuilder::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([$this->getMockParameterBag()])
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->onlyMethods(['fileExists'])
            ->getMock();

        $mock->expects(self::exactly(1))
            ->method('fileExists')
            ->withConsecutive(
                [self::equalTo('/app/src/DependencyInjection/../../config/services.xml')],
            );

        return $mock;
    }

    protected function getMockParameterBag(): ParameterBagInterface
    {
        return $this->createPartialMock(ParameterBag::class, []);
    }
}
