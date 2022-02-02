<?php

namespace Lle\HermesBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MailTrackerController extends AbstractController
{
    protected $em;
    protected $parameterBag;

    public function __construct(EntityManagerInterface $em, ParameterBagInterface $parameterBag)
    {
        $this->em = $em;
        $this->parameterBag = $parameterBag;
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
     * @Route("/mailOpened/{destinataire}", name="mail_opened")
     */
    public function mailOpened(Destinataire $destinataire)
    {
        $destinataire->setDateOuverture(new \DateTime('now'));
        $this->em->persist($destinataire);
        $this->em->flush();

        $destinatairesOpen = $this->em->getRepository(Destinataire::class)->countDestinatairesOpen($destinataire->getMail());
        $this->em->getRepository(Mail::class)->updateTotalOpened($destinataire->getMail(), $destinatairesOpen['count']);

        $img = $this->rootDir . '/public/img/pixel.png';
        $headers = ['Content-Type' => 'image/png'];

        return new BinaryFileResponse($img, 200, $headers);
    }

}
