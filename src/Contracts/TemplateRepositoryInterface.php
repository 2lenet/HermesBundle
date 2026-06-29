<?php

namespace Lle\HermesBundle\Contracts;

use Doctrine\Persistence\ObjectRepository;

interface TemplateRepositoryInterface extends ObjectRepository
{
    public function duplicateTemplate(TemplateInterface $template, string $code): TemplateInterface;
}
