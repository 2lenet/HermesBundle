<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Tests\Command;

use Lle\HermesBundle\Command\CheckTemplatesCommand;
use Lle\HermesBundle\Model\TemplateIssue;
use Lle\HermesBundle\Service\MailTemplateAuditor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CheckTemplatesCommandTest extends TestCase
{
    private const ERROR = 'Filter "map" is not allowed. (line 1)';

    public function testSucceedsWhenEveryTemplateIsAllowed(): void
    {
        $commandTester = $this->createCommandTester([]);

        $status = $commandTester->execute([]);

        self::assertSame(Command::SUCCESS, $status);
        self::assertStringContainsString(CheckTemplatesCommand::SUCCESS_MESSAGE, $commandTester->getDisplay());
    }

    public function testListsRefusedTemplatesAndFails(): void
    {
        $issue = new TemplateIssue(MailTemplateAuditor::SOURCE_TEMPLATE, 12, 'html', self::ERROR);
        $commandTester = $this->createCommandTester([$issue]);

        $status = $commandTester->execute([]);

        self::assertSame(Command::FAILURE, $status);
        $display = $commandTester->getDisplay();
        self::assertStringContainsString(MailTemplateAuditor::SOURCE_TEMPLATE, $display);
        self::assertStringContainsString('12', $display);
        self::assertStringContainsString(self::ERROR, $display);
    }

    /**
     * @param TemplateIssue[] $issues
     */
    private function createCommandTester(array $issues): CommandTester
    {
        $auditor = $this->createMock(MailTemplateAuditor::class);
        $auditor->method('audit')->willReturn($issues);
        $application = new Application();
        $application->addCommand(new CheckTemplatesCommand($auditor));

        return new CommandTester($application->find(CheckTemplatesCommand::NAME));
    }
}
