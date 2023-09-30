<?php

namespace phpunit\Unit\Command;

use Lle\HermesBundle\Command\SendCommand;
use Lle\HermesBundle\Service\Sender;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class SendCommandTest
 * @package phpunit\Unit\Command
 *
 * @author 2LE <2le@2le.net>
 */
class SendCommandTest extends TestCase
{
    private MockObject $sender;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sender = $this->createMock(Sender::class);
        $application = new Application();
        $application->add(new SendCommand($this->sender));
        $command = $application->find('lle:hermes:send');
        $this->commandTester = new CommandTester($command);
    }

    public function testExecute(): void
    {
        $this->sender
            ->expects(self::exactly(1))
            ->method('sendAllMails')
            ->with(self::equalTo(15));
        $output = $this->commandTester->execute(['--nb' => 15]);
        self::assertEquals(Command::SUCCESS, $output);
        self::assertEquals('[OK] Success 0 mails sent', trim($this->commandTester->getDisplay()));
    }
}
