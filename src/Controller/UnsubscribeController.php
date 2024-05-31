<?php

namespace Lle\HermesBundle\Controller;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\UnsubscribeEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UnsubscribeController extends AbstractController
{
    protected string $secret;

    public function __construct(
        protected readonly EntityManagerInterface $em,
        ParameterBagInterface $parameters
    ) {
        /** @var string $secret */
        $secret = $parameters->get('lle_hermes.app_secret');
        $this->secret = $secret;
    }

    #[Route('/unsubscribe/{email}/{token}', name: 'unsubscribe', methods: ['GET'])]
    public function unsubscribe(string $email, string $token): Response
    {
        $expected = md5($email . $this->secret);

        if ($token !== $expected) {
            return $this->render('@LleHermes/unsubscribe/error.html.twig');
        }

        if ($this->isAlreadyUnsubscribe($email)) {
            return $this->render('@LleHermes/unsubscribe/already_unsubscribe.html.twig');
        }

        return $this->render('@LleHermes/unsubscribe/index.html.twig', ['email' => $email, 'token' => $token]);
    }

    #[Route('/unsubscribe/confirm/{email}/{token}', name: 'confirm_unsubscribe', methods: ['GET'])]
    public function confirmUnsubscribtion(string $email, string $token): Response
    {
        $expected = md5($email . $this->secret);

        if ($token !== $expected) {
            return $this->render('@LleHermes/unsubscribe/error.html.twig');
        }

        if ($this->isAlreadyUnsubscribe($email)) {
            return $this->render('@LleHermes/unsubscribe/already_unsubscribe.html.twig');
        }

        $unsubscribe = new UnsubscribeEmail();
        $unsubscribe->setEmail($email);
        $unsubscribe->setUnsubscribeDate(new DateTime());
        $this->em->persist($unsubscribe);
        $this->em->flush();

        return $this->render('@LleHermes/unsubscribe/confirm.html.twig');
    }

    #[Route('/unsubscribe/cancel/{email}/{token}', name: 'cancel_unsubscribe', methods: ['GET'])]
    public function cancelUnsubscribtion(string $email, string $token): Response
    {
        $expected = md5($email . $this->secret);

        if ($token !== $expected) {
            return $this->render('@LleHermes/unsubscribe/error.html.twig');
        }

        if ($this->isAlreadyUnsubscribe($email)) {
            return $this->render('@LleHermes/unsubscribe/already_unsubscribe.html.twig');
        }

        return $this->render('@LleHermes/unsubscribe/cancel.html.twig');
    }

    private function isAlreadyUnsubscribe(string $email): bool
    {
        if ($this->em->getRepository(UnsubscribeEmail::class)->findBy(['email' => $email])) {
            return true;
        }

        return false;
    }
}
