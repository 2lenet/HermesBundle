<?php

namespace phpunit\Unit\Command;

use Lle\HermesBundle\Command\SendCommand;
use Lle\HermesBundle\Service\SenderService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class SendCommandTest
 * @package phpunit\Unit\Command
 *
 * @author 2LE <2le@2le.net>
 */
class SendCommandTest extends TestCase
{
    private MockObject $senderService;
    private CommandTester $commandTester;

    public function testExecute(): void
    {
        $this->senderService
            ->expects(self::exactly(1))
            ->method('sendAllMail')
            ->with(self::equalTo(15));
        $output = $this->commandTester->execute(['--nb' => 15]);
        self::assertEquals(0, $output);
        self::assertEquals('[OK] Success', trim($this->commandTester->getDisplay()));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->senderService = $this->createMock(SenderService::class);
        $application = new Application();
        $application->add(new SendCommand($this->senderService));
        $command = $application->find('lle:hermes:send');
        $this->commandTester = new CommandTester($command);
    }
}
