<?php

namespace Lle\HermesBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\Mail;
use Lle\HermesBundle\Entity\Recipient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class MailTrackerController extends AbstractController
{
    protected $em;
    protected $parameterBag;
    protected $kernel;

    public function __construct(EntityManagerInterface $em, ParameterBagInterface $parameterBag, KernelInterface $kernel)
    {
        $this->em = $em;
        $this->parameterBag = $parameterBag;
        $this->kernel = $kernel;
    }

    /**
     * @Route("/unsubscribe/{email}/{token}", name="unsubscribe")
     */
    public function unsubscribe($email, $token)
    {
        $expected = md5($email . $this->appSecret);
        if ($token != $expected) {
            return $this->render('unsubscribe/confirm_unsubscribe.html.twig');
        } else {
            $unsubscribe = new UnsubscribeEmail();
            $unsubscribe->setEmail($email);
            $unsubscribe->setDateUnsuscribe(new \DateTime('now'));
            $this->em->persist($unsubscribe);
            $this->em->flush();

            return $this->render('unsubscribe/confirm_unsubscribe.html.twig');
        }
    }

    /**
     * @Route("/mailOpened/{recipient}", name="mail_opened")
     */
    public function mailOpened(Recipient $recipient)
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
