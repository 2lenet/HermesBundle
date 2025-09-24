<?php

namespace Lle\HermesBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lle\HermesBundle\Entity\TypeTemplate;

/**
 * @method TypeTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method TypeTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method TypeTemplate[]    findAll()
 * @method TypeTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeTemplate::class);
    }
}
