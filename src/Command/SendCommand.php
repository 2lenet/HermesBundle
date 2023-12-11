<?php

namespace Lle\HermesBundle\Command;

use Lle\HermesBundle\Service\Sender;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Class SendCommand
 * @package Lle\HermesBundle\Command
 */
#[AsCommand(name: 'lle:hermes:send')]
class SendCommand extends Command
{
    use LockableTrait;

    public function __construct(protected readonly Sender $sender)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Send waiting email')
            ->addOption('nb', null, InputOption::VALUE_OPTIONAL, 'Option description', 10);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process');

            return Command::FAILURE;
        }

        $io = new SymfonyStyle($input, $output);

        $nb = $input->getOption('nb');

        $nbSent = $this->sender->sendAllMails($nb);

        $io->success("Success $nbSent mails sent");

        return Command::SUCCESS;
    }
}
