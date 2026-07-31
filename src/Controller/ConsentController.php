<?php

namespace Lle\HermesBundle\Controller;

use Lle\HermesBundle\Entity\Consent;
use Lle\HermesBundle\Service\ConsentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ConsentController extends AbstractController
{
    protected string $secret;

    public function __construct(
        protected readonly ConsentManager $consentManager,
        ParameterBagInterface $parameters,
    ) {
        /** @var string $secret */
        $secret = $parameters->get('lle_hermes.app_secret');
        $this->secret = $secret;
    }

    #[Route(
        '/consent/{email}/{value}/{token}',
        name: 'consent_manage',
        requirements: ['value' => '0|1'],
        methods: ['GET'],
    )]
    public function manage(string $email, string $value, string $token): Response
    {
        if (!$this->isTokenValid($email, $value, $token)) {
            return $this->render('@LleHermes/consent/error.html.twig');
        }

        return $this->render('@LleHermes/consent/index.html.twig', [
            'email' => $email,
            'acceptUrl' => $this->generateConfirmUrl($email, '1'),
            'refuseUrl' => $this->generateConfirmUrl($email, '0'),
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
        if (!$this->isTokenValid($email, $value, $token)) {
            return $this->render('@LleHermes/consent/error.html.twig');
        }

        $boolValue = $value === '1';
        $this->consentManager->setConsent($email, Consent::TYPE_PIXEL, $boolValue);

        return $this->render('@LleHermes/consent/confirm.html.twig', ['value' => $boolValue]);
    }

    private function generateConfirmUrl(string $email, string $value): string
    {
        return $this->generateUrl('confirm_consent', [
            'email' => $email,
            'value' => $value,
            'token' => md5($email . $value . $this->secret),
        ]);
    }

    private function isTokenValid(string $email, string $value, string $token): bool
    {
        return $token === md5($email . $value . $this->secret);
    }
}
