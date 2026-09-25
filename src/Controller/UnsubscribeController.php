<?php

namespace Lle\HermesBundle\Controller;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\TypeTemplate;
use Lle\HermesBundle\Entity\UnsubscribeEmail;
use Lle\HermesBundle\Repository\TypeTemplateRepository;
use Lle\HermesBundle\Repository\UnsubscribeEmailRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UnsubscribeController extends AbstractController
{
    protected string $secret;

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly UnsubscribeEmailRepository $unsubscribeEmailRepository,
        protected readonly TypeTemplateRepository $typeTemplateRepository,
        ParameterBagInterface $parameters
    ) {
        /** @var string $secret */
        $secret = $parameters->get('lle_hermes.app_secret');
        $this->secret = $secret;
    }

    #[Route('/unsubscribe/{email}/{token}', name: 'unsubscribe', methods: ['GET'])]
    public function unsubscribe(Request $request, string $email, string $token): Response
    {
        if (!$this->isTokenValid($email, $token)) {
            return $this->render('@LleHermes/unsubscribe/error.html.twig');
        }

        if ($this->typeTemplateRepository->count([]) === 0) {
            if ($this->isAlreadyUnsubscribe($email)) {
                return $this->render('@LleHermes/unsubscribe/already_unsubscribe.html.twig');
            }

            return $this->render('@LleHermes/unsubscribe/index.html.twig', ['email' => $email, 'token' => $token]);
        }

        $unsubscriptions = $this->unsubscribeEmailRepository->findByTypeForEmail($email);
        $receivedTypeCode = $request->query->get('type');

        $choices = [];
        $receivedTypeTemplate = null;
        foreach ($this->typeTemplateRepository->findUnsubscribable() as $typeTemplate) {
            $isReceivedType = $typeTemplate->getCode() === $receivedTypeCode;
            if ($isReceivedType) {
                $receivedTypeTemplate = $typeTemplate;
            }

            $choices[] = [
                'typeTemplate' => $typeTemplate,
                'subscribed' => !isset($unsubscriptions[(int)$typeTemplate->getId()]) && !$isReceivedType,
            ];
        }

        return $this->render('@LleHermes/unsubscribe/types.html.twig', [
            'email' => $email,
            'token' => $token,
            'choices' => $choices,
            'receivedTypeTemplate' => $receivedTypeTemplate,
            'unsubscribedFromAll' => $this->isAlreadyUnsubscribe($email),
        ]);
    }

    #[Route('/unsubscribe/save/{email}/{token}', name: 'save_unsubscribe', methods: ['POST'])]
    public function saveUnsubscriptions(Request $request, string $email, string $token): Response
    {
        if (!$this->isTokenValid($email, $token)) {
            return $this->render('@LleHermes/unsubscribe/error.html.twig');
        }

        $globalUnsubscription = $this->unsubscribeEmailRepository->findGlobalByEmail($email);
        if ($request->request->getBoolean('unsubscribeAll')) {
            if (!$globalUnsubscription) {
                $this->em->persist($this->createUnsubscription($email));
                $this->em->flush();
            }

            return $this->render('@LleHermes/unsubscribe/saved.html.twig');
        }

        if ($globalUnsubscription) {
            $this->em->remove($globalUnsubscription);
        }

        $subscribedIds = array_map('intval', $request->request->all('subscribed'));
        $unsubscriptions = $this->unsubscribeEmailRepository->findByTypeForEmail($email);

        foreach ($this->typeTemplateRepository->findUnsubscribable() as $typeTemplate) {
            $typeTemplateId = (int)$typeTemplate->getId();
            $isSubscribed = in_array($typeTemplateId, $subscribedIds, true);
            $unsubscription = $unsubscriptions[$typeTemplateId] ?? null;

            if (!$isSubscribed && !$unsubscription) {
                $this->em->persist($this->createUnsubscription($email, $typeTemplate));
            } elseif ($isSubscribed && $unsubscription) {
                $this->em->remove($unsubscription);
            }
        }

        $this->em->flush();

        return $this->render('@LleHermes/unsubscribe/saved.html.twig');
    }

    #[Route('/unsubscribe/confirm/{email}/{token}', name: 'confirm_unsubscribe', methods: ['GET'])]
    public function confirmUnsubscribtion(string $email, string $token): Response
    {
        if (!$this->isTokenValid($email, $token)) {
            return $this->render('@LleHermes/unsubscribe/error.html.twig');
        }

        if ($this->isAlreadyUnsubscribe($email)) {
            return $this->render('@LleHermes/unsubscribe/already_unsubscribe.html.twig');
        }

        $this->em->persist($this->createUnsubscription($email));
        $this->em->flush();

        return $this->render('@LleHermes/unsubscribe/confirm.html.twig');
    }

    #[Route('/unsubscribe/cancel/{email}/{token}', name: 'cancel_unsubscribe', methods: ['GET'])]
    public function cancelUnsubscribtion(string $email, string $token): Response
    {
        if (!$this->isTokenValid($email, $token)) {
            return $this->render('@LleHermes/unsubscribe/error.html.twig');
        }

        if ($this->isAlreadyUnsubscribe($email)) {
            return $this->render('@LleHermes/unsubscribe/already_unsubscribe.html.twig');
        }

        return $this->render('@LleHermes/unsubscribe/cancel.html.twig');
    }

    public function createUnsubscription(string $email, ?TypeTemplate $typeTemplate = null): UnsubscribeEmail
    {
        $unsubscription = new UnsubscribeEmail();
        $unsubscription->setEmail(strtolower($email))
            ->setUnsubscribeDate(new DateTime())
            ->setTypeTemplate($typeTemplate);

        return $unsubscription;
    }

    public function isTokenValid(string $email, string $token): bool
    {
        return $token === md5($email . $this->secret);
    }

    public function isAlreadyUnsubscribe(string $email): bool
    {
        return $this->unsubscribeEmailRepository->findGlobalByEmail($email) !== null;
    }
}
