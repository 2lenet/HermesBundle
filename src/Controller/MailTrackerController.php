<?php

namespace Lle\HermesBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class MailTrackerController extends AbstractController
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly KernelInterface $kernel,
    ) {
    }

    #[Route('/mailOpened/{recipient}', name: 'mail_opened', methods: ['GET'])]
    public function mailOpened(Recipient $recipient): BinaryFileResponse
    {
        $recipient->setOpenDate(new \DateTime());
        $this->em->persist($recipient);
        $this->em->flush();

        /** @var Mail $mail */
        $mail = $recipient->getMail();
        $recipientsOpen = $this->em->getRepository(Recipient::class)->countOpenRecipients($mail);
        $this->em->getRepository(Mail::class)->updateTotalOpened($mail, $recipientsOpen);

        $image = $this->kernel->getProjectDir() . '/vendor/2lenet/hermes-bundle/assets/img/pixel.png';
        $headers = ['Content-Type' => 'image/png'];

        return new BinaryFileResponse($image, Response::HTTP_OK, $headers);
    }
}
