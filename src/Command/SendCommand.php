<?php

namespace Lle\HermesBundle\Command;

use Lle\HermesBundle\Service\SenderService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class SendCommand
 * @package Lle\HermesBundle\Command
 *
 */
class SendCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'lle:hermes:send';

    private SenderService $sender;

    public function __construct(SenderService $sender)
    {
        $this->sender = $sender;
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

            return 0;
        }

        $io = new SymfonyStyle($input, $output);

        $nb = $input->getOption('nb');

        $nbSent = $this->sender->sendAllMails($nb);

        $io->success("Success $nbSent mails sent");

        return 0;
    }
}
