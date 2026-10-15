<?php

namespace Lle\HermesBundle\Controller;

use Lle\HermesBundle\Entity\Consent;
use Lle\HermesBundle\Service\ConsentManager;
use Lle\HermesBundle\Service\ConsentTokenManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ConsentController extends AbstractController
{
    public function __construct(
        protected readonly ConsentManager $consentManager,
        protected readonly ConsentTokenManager $tokenManager,
    ) {
    }

    #[Route(
        '/consent/{email}/{token}',
        name: 'consent_manage',
        methods: ['GET'],
    )]
    public function manage(string $email, string $token): Response
    {
        if (!$this->tokenManager->isTokenValid($email, $token)) {
            return $this->render('@LleHermes/consent/error.html.twig');
        }

        return $this->render('@LleHermes/consent/index.html.twig', [
            'email' => $email,
            'acceptUrl' => $this->generateUrl('confirm_consent', [
                'email' => $email,
                'value' => '1',
                'token' => $this->tokenManager->getConfirmToken($email, true)
            ]),
            'refuseUrl' => $this->generateUrl('confirm_consent', [
                'email' => $email,
                'value' => '0',
                'token' => $this->tokenManager->getConfirmToken($email, false)
            ]),
        ]);
    }

    #[Route(
        '/consent/confirm/{email}/{value}/{token}',
        name: 'confirm_consent',
        requirements: ['value' => '0|1'],
        methods: ['GET'],
    )]
    public function confirm(string $email, string $value, string $token): Response
    {
        $boolValue = $value === '1';

        if (!$this->tokenManager->isConfirmTokenValid($email, $boolValue, $token)) {
            return $this->render('@LleHermes/consent/error.html.twig');
        }

        $this->consentManager->setConsent($email, Consent::TYPE_TRACKING, $boolValue);

        return $this->render('@LleHermes/consent/confirm.html.twig', ['value' => $boolValue]);
    }
}
