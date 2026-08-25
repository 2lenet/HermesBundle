<?php

namespace Lle\HermesBundle\Twig;

use Lle\HermesBundle\Entity\Consent;
use Lle\HermesBundle\Service\ConsentManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class HermesConsentExtension extends AbstractExtension
{
    public function __construct(
        private readonly ConsentManager $consentManager,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('hermes_has_consent', $this->hasConsent(...)),
        ];
    }

    public function hasConsent(string $email): bool
    {
        return $this->consentManager->hasConsent($email, Consent::TYPE_TRACKING);
    }
}
