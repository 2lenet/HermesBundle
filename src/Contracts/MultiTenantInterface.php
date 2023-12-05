<?php

namespace Lle\HermesBundle\Contracts;

interface MultiTenantInterface
{
    public function getTenantId(): int;
}
