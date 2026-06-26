<?php

namespace Lle\HermesBundle\Repository;

use Lle\HermesBundle\Contracts\TemplateInterface;

interface TemplateRepositoryInterface
{
    public function find(mixed $id, mixed $lockMode = null, mixed $lockVersion = null): ?TemplateInterface;

    public function findOneBy(array $criteria, ?array $orderBy = null): ?TemplateInterface;

    /** @return TemplateInterface[] */
    public function findAll(): array;

    /** @return TemplateInterface[] */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    public function duplicateTemplate(TemplateInterface $template, string $code): TemplateInterface;
}
