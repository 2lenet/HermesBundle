<?php

namespace Lle\HermesBundle\Service;

use Lle\HermesBundle\Contracts\MultiTenantInterface;
use Lle\HermesBundle\Entity\Mail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MultiTenantManager
{
    public function __construct(
        private readonly ParameterBagInterface $parameters,
        private readonly Security $security
    ) {
    }

    public function isMultiTenantEnabled(): bool
    {
        if ($this->parameters->get('lle_hermes.tenant_class')) {
            return true;
        }

        return false;
    }

    public function getTenantId(): ?int
    {
        /** @var ?MultiTenantInterface $user */
        $user = $this->security->getUser();

        return $user?->getTenantId();
    }

    public function getTenantClass(): ?string
    {
        /** @var ?class-string $class */
        $class = $this->parameters->get('lle_hermes.tenant_class');

        return $class;
    }

    public function isOwner(MultiTenantInterface $object): bool
    {
        if ($this->isMultiTenantEnabled()) {
            if ($object->getTenantId() !== $this->getTenantId()) {
                return false;
            }
        }

        return true;
    }
}
