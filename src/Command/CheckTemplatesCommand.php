<?php

declare(strict_types=1);

namespace Lle\HermesBundle\Command;

use Lle\HermesBundle\Service\MailTemplateAuditor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Lists the templates and the mails not sent yet that the mail Twig environment refuses (sandbox,
 * syntax). To run before deploying a version enabling or tightening the sandbox.
 */
#[AsCommand(name: self::NAME, description: self::DESCRIPTION)]
class CheckTemplatesCommand extends Command
{
    public const NAME = 'lle:hermes:check-templates';
    public const DESCRIPTION = 'Check the mail templates and the mails not sent yet against the mail Twig sandbox';
    public const SUCCESS_MESSAGE = 'Every mail template is allowed.';
    public const FAILURE_MESSAGE = '%d template field(s) refused: fix them or allow the construct in '
        . '"lle_hermes.mail_twig.sandbox".';
    public const HEADERS = ['Source', 'Id', 'Field', 'Error'];

    public function __construct(protected MailTemplateAuditor $mailTemplateAuditor)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $issues = $this->mailTemplateAuditor->audit();

        if ($issues === []) {
            $io->success(self::SUCCESS_MESSAGE);

            return Command::SUCCESS;
        }

        $rows = [];
        foreach ($issues as $issue) {
            $rows[] = [$issue->getSource(), $issue->getId(), $issue->getField(), $issue->getError()];
        }
        $io->table(self::HEADERS, $rows);
        $io->error(sprintf(self::FAILURE_MESSAGE, count($issues)));

        return Command::FAILURE;
    }
}
