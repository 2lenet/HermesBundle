<?php

namespace Lle\HermesBundle\Command;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lle\EntityFileBundle\Service\EntityFileLoader;
use Lle\HermesBundle\Crudit\Config\MailCrudConfig;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Service\AttachmentService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Console\Command\LockableTrait;

#[AsCommand(name: 'lle:hermes:delete_attachments')]
class DeleteAttachmentsCommand extends Command
{
    use LockableTrait;

    public function __construct(
        protected AttachmentService $attachmentService,
        protected EntityManagerInterface $em,
        protected ParameterBagInterface $parameters,
        protected EntityFileLoader $entityFileLoader,
    ) {
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process');

            return Command::FAILURE;
        }

        $io = new SymfonyStyle($input, $output);

        /** @var string $nbDays */
        $nbDays = $this->parameters->get('lle_hermes.attachment_nb_days_before_deletion');
        $date = new DateTime('-' . $nbDays . ' days');
        $mails = $this->em->getRepository(Mail::class)->findOldMails($date);
        $count = 0;

        foreach ($mails as $mail) {
            $this->attachmentService->deleteAttachements($mail);
            $entityConfig = $this->entityFileLoader->get(MailCrudConfig::MAIL_ATTACHED_FILE_CONFIG);
            foreach ($entityConfig->get($mail) as $file) {
                $entityConfig->delete($file);
            }

            $mail->setAttachmentsDeleted(true);
            $count++;

            if ($count % 1000 === 0) {
                $this->em->flush();
            }
        }

        $this->em->flush();



        $io->success("Success attachments deleted for $count mail(s)");

        return Command::SUCCESS;
    }
}
