<?php

namespace Lle\HermesBundle\Command;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Service\AttachmentService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'lle:hermes:delete_attachments')]
class DeleteAttachments extends Command
{
    public function __construct(
        protected AttachmentService $attachmentService,
        protected EntityManagerInterface $em,
        private ParameterBagInterface $parameters,
    ) {
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $nbDays = $this->parameters->get('lle_hermes.attachment_nb_days');
        $date = new DateTime('-' . $nbDays . ' days');
        $mails = $this->em->getRepository(Mail::class)->findOldMails($date);
        $count = 0;

        foreach($mails as $mail) {
            $this->attachmentService->deleteAttachements($mail);
            $mail->setAttachmentDeleted(true);
            $count++;
        }

        $this->em->flush();

        $io->success("Success attachments deleted for $count mail(s)");

        return Command::SUCCESS;
    }
}