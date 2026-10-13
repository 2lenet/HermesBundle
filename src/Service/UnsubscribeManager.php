<?php

namespace Lle\HermesBundle\Service;

use DateTime;
use Lle\HermesBundle\Entity\TypeTemplate;
use Lle\HermesBundle\Entity\UnsubscribeEmail;
use Lle\HermesBundle\Repository\UnsubscribeEmailRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UnsubscribeManager
{
    protected readonly string $secret;

    public function __construct(
        protected readonly UnsubscribeEmailRepository $unsubscribeEmailRepository,
        ParameterBagInterface $parameters,
    ) {
        /** @var string $secret */
        $secret = $parameters->get('lle_hermes.app_secret');
        $this->secret = $secret;
    }

    /**
     * The type is signed in the token so that it can't be changed in the link.
     * Without type, the token stays the same as the one of the links sent before types existed.
     */
    public function getToken(string $email, ?TypeTemplate $typeTemplate = null): string
    {
        return md5($email . ($typeTemplate?->getCode() ?? '') . $this->secret);
    }

    public function isTokenValid(string $email, string $token, ?TypeTemplate $typeTemplate = null): bool
    {
        return $token === $this->getToken($email, $typeTemplate);
    }

    public function createUnsubscription(string $email, ?TypeTemplate $typeTemplate = null): UnsubscribeEmail
    {
        $unsubscription = new UnsubscribeEmail();
        $unsubscription->setEmail(strtolower($email))
            ->setUnsubscribeDate(new DateTime())
            ->setTypeTemplate($typeTemplate);

        return $unsubscription;
    }

    public function isAlreadyUnsubscribed(string $email): bool
    {
        return $this->unsubscribeEmailRepository->findGlobalByEmail($email) !== null;
    }
}
