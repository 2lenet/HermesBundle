<?php

namespace Lle\HermesBundle\Service;

use Lle\HermesBundle\Contracts\MultiTenantInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MultiTenantService
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
        private readonly Security $security
    ) {
    }

    public function isMultiTenantEnable(): bool
    {
        if ($this->parameterBag->get('lle_hermes.tenant_class')) {
            return true;
        }

        return false;
    }

    public function getTenantId(): int
    {
        /** @var MultiTenantInterface $user */
        $user = $this->security->getUser();

        return $user->getTenantId();
    }

    public function getTenantClass(): ?string
    {
        /** @var ?class-string $class */
        $class = $this->parameterBag->get('lle_hermes.tenant_class');

        return $class;
    }
}
