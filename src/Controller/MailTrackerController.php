<?php

namespace Lle\HermesBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class MailTrackerController extends AbstractController
{
    protected EntityManagerInterface $em;
    protected ParameterBagInterface $parameterBag;
    protected KernelInterface $kernel;

    public function __construct(EntityManagerInterface $em, ParameterBagInterface $parameterBag, KernelInterface $kernel)
    {
        $this->em = $em;
        $this->parameterBag = $parameterBag;
        $this->kernel = $kernel;
    }

    /**
     * @Route("/mailOpened/{recipient}", name="mail_opened")
     */
    public function mailOpened(Recipient $recipient): BinaryFileResponse
    {
        $recipient->setOpenDate(new \DateTime('now'));
        $this->em->persist($recipient);
        $this->em->flush();

        $recipientsOpen = $this->em->getRepository(Recipient::class)->countOpenRecipient($recipient->getMail());
        $this->em->getRepository(Mail::class)->updateTotalOpened($recipient->getMail(), $recipientsOpen);

        $image = $this->kernel->getProjectDir() . '/vendor/2lenet/hermes-bundle/assets/img/pixel.png';
        $headers = ['Content-Type' => 'image/png'];

        return new BinaryFileResponse($image, 200, $headers);
    }

}
