<?php

namespace Lle\HermesBundle\Command;

use Lle\HermesBundle\Service\MailError\MailRecoverer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ErrorRetrievalCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'lle:hermes:recover-errors';

    public function __construct(protected readonly MailRecoverer $mailRecoverer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Retrieve emails in error and add them to database')
            ->addOption('nb', null, InputOption::VALUE_OPTIONAL, 'Number of mails by batch', 50);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process');

            return Command::FAILURE;
        }

        $io = new SymfonyStyle($input, $output);

        $nb = $input->getOption('nb');
        $nbRecovered = $this->mailRecoverer->recoverAll($nb);

        $io->success("Success $nbRecovered mails recovered");

        return Command::SUCCESS;
    }
}
