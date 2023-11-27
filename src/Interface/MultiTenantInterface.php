<?php

namespace Lle\HermesBundle\Interface;

interface MultiTenantInterface
{
    public function getTenantId(): int;
}