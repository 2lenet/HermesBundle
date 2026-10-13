<?php

namespace Lle\HermesBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Lle\HermesBundle\Entity\TypeTemplate;
use Lle\HermesBundle\Repository\TypeTemplateRepository;
use Lle\HermesBundle\Repository\UnsubscribeEmailRepository;
use Lle\HermesBundle\Service\UnsubscribeManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UnsubscribeController extends AbstractController
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly UnsubscribeEmailRepository $unsubscribeEmailRepository,
        protected readonly TypeTemplateRepository $typeTemplateRepository,
        protected readonly UnsubscribeManager $unsubscribeManager,
    ) {
    }

    #[Route(
        '/unsubscribe/{email}/{token}/{type}',
        name: 'unsubscribe',
        defaults: ['type' => null],
        methods: ['GET'],
    )]
    public function unsubscribe(
        string $email,
        string $token,
        #[MapEntity(mapping: ['type' => 'code'])] ?TypeTemplate $receivedTypeTemplate = null,
    ): Response {
        if (!$this->unsubscribeManager->isTokenValid($email, $token, $receivedTypeTemplate)) {
            return $this->render('@LleHermes/unsubscribe/error.html.twig');
        }

        if ($this->typeTemplateRepository->count([]) === 0) {
            if ($this->unsubscribeManager->isAlreadyUnsubscribed($email)) {
                return $this->render('@LleHermes/unsubscribe/already_unsubscribe.html.twig');
            }

            return $this->render('@LleHermes/unsubscribe/index.html.twig', ['email' => $email, 'token' => $token]);
        }

        $unsubscriptions = $this->unsubscribeEmailRepository->findByTypeForEmail($email);

        $choices = [];
        foreach ($this->typeTemplateRepository->findUnsubscribable() as $typeTemplate) {
            $isReceivedType = $typeTemplate->getId() === $receivedTypeTemplate?->getId();

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
            'unsubscribedFromAll' => $this->unsubscribeManager->isAlreadyUnsubscribed($email),
        ]);
    }

    #[Route(
        '/unsubscribe/save/{email}/{token}/{type}',
        name: 'save_unsubscribe',
        defaults: ['type' => null],
        methods: ['POST'],
    )]
    public function saveUnsubscriptions(
        Request $request,
        string $email,
        string $token,
        #[MapEntity(mapping: ['type' => 'code'])] ?TypeTemplate $receivedTypeTemplate = null,
    ): Response {
        if (!$this->unsubscribeManager->isTokenValid($email, $token, $receivedTypeTemplate)) {
            return $this->render('@LleHermes/unsubscribe/error.html.twig');
        }

        $globalUnsubscription = $this->unsubscribeEmailRepository->findGlobalByEmail($email);
        if ($request->request->getBoolean('unsubscribeAll')) {
            if (!$globalUnsubscription) {
                $this->em->persist($this->unsubscribeManager->createUnsubscription($email));
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
                $this->em->persist($this->unsubscribeManager->createUnsubscription($email, $typeTemplate));
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
        if (!$this->unsubscribeManager->isTokenValid($email, $token)) {
            return $this->render('@LleHermes/unsubscribe/error.html.twig');
        }

        if ($this->unsubscribeManager->isAlreadyUnsubscribed($email)) {
            return $this->render('@LleHermes/unsubscribe/already_unsubscribe.html.twig');
        }

        $this->em->persist($this->unsubscribeManager->createUnsubscription($email));
        $this->em->flush();

        return $this->render('@LleHermes/unsubscribe/confirm.html.twig');
    }

    #[Route('/unsubscribe/cancel/{email}/{token}', name: 'cancel_unsubscribe', methods: ['GET'])]
    public function cancelUnsubscribtion(string $email, string $token): Response
    {
        if (!$this->unsubscribeManager->isTokenValid($email, $token)) {
            return $this->render('@LleHermes/unsubscribe/error.html.twig');
        }

        if ($this->unsubscribeManager->isAlreadyUnsubscribed($email)) {
            return $this->render('@LleHermes/unsubscribe/already_unsubscribe.html.twig');
        }

        return $this->render('@LleHermes/unsubscribe/cancel.html.twig');
    }
}
