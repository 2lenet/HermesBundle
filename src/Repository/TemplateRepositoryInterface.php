<?php

namespace Lle\HermesBundle\Repository;

use Doctrine\Persistence\ObjectRepository;
use Lle\HermesBundle\Contracts\TemplateInterface;

interface TemplateRepositoryInterface extends ObjectRepository
{
    public function duplicateTemplate(TemplateInterface $template, string $code): TemplateInterface;
}
