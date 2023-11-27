<?php

namespace Lle\HermesBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lle\HermesBundle\Entity\EmailError;

/**
 * Class EmailErrorRepository
 * @package Lle\HermesBundle\Repository
 *
 * @author 2LE <2le@2le.net>
 *
 * @method EmailError|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmailError|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmailError[]    findAll()
 * @method EmailError[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailErrorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailError::class);
    }

    public function findEmailsInError(int $limit): array
    {
        return $this->createQueryBuilder('emailError')
            ->select('emailError.email as email')
            ->where('emailError.nbError >= :limit')
            ->setParameter('limit', $limit)
            ->getQuery()
            ->getScalarResult();
    }
}
